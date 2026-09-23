<?php

namespace App\Http\Controllers;

use App\Models\Incidente;
use App\Models\AuditLog;
use App\Models\User;
use App\Mail\NovoIncidenteNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Embarcacao;
use App\Models\Terminal;
use Barryvdh\DomPDF\Facade\Pdf;

class IncidenteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('role:admin|operador|concessionaria')->except(['index', 'show', 'api']);
        $this->middleware('role:admin|operador')->only(['create', 'store', 'edit', 'update', 'destroy', 'investigate', 'close']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Incidente::with(['user', 'embarcacao', 'terminal', 'responsavelInvestigacao']);

            // Filtros
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('titulo', 'like', "%{$search}%")
                      ->orWhere('descricao', 'like', "%{$search}%")
                      ->orWhere('numero_incidente', 'like', "%{$search}%")
                      ->orWhere('local_ocorrencia', 'like', "%{$search}%");
                });
            }

            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            if ($request->filled('gravidade')) {
                $query->where('gravidade', $request->gravidade);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('data_inicio') && $request->filled('data_fim')) {
                $query->whereBetween('data_ocorrencia', [
                    Carbon::parse($request->data_inicio)->startOfDay(),
                    Carbon::parse($request->data_fim)->endOfDay()
                ]);
            }

            // Filtro por usuário se for concessionária
            if (Auth::user()->hasRole('concessionaria')) {
                $query->where('user_id', Auth::id());
            }

            $incidentes = $query->ativo()
                              ->orderBy('gravidade', 'desc')
                              ->orderBy('data_ocorrencia', 'desc')
                              ->paginate(15);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'view',
                'auditable_type' => 'Incidente',
                'auditable_id' => null,
                'description' => 'Visualizou listagem de incidentes',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            return view('incidentes.index', compact('incidentes'));

        } catch (\Exception $e) {
            Log::error('Erro ao listar incidentes: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar incidentes.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         $embarcacoes = Embarcacao::where('is_active', true)->get();
         $terminais = Terminal::where('is_active', true)->get();
        try {
            return view('incidentes.create',  compact('embarcacoes', 'terminais'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de criação de incidente: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string|max:2000',
            'tipo' => 'required|in:acidente,avaria,poluicao,seguranca,operacional,outros',
            'gravidade' => 'required|in:baixa,media,alta,critica',
            'data_ocorrencia' => 'required|date|before_or_equal:now',
            'local_ocorrencia' => 'required|string|max:255',
            'embarcacao_id' => 'nullable|exists:embarcacoes,id',
            'terminal_id' => 'nullable|exists:terminais,id',
            'pessoas_envolvidas' => 'nullable|array',
            'pessoas_envolvidas.*.nome' => 'required_with:pessoas_envolvidas|string|max:255',
            'pessoas_envolvidas.*.cargo' => 'nullable|string|max:255',
            'pessoas_envolvidas.*.empresa' => 'nullable|string|max:255',
            'causas_identificadas' => 'nullable|string|max:2000',
            'acoes_imediatas' => 'nullable|string|max:2000',
            'acoes_corretivas' => 'nullable|string|max:2000',
            'requer_notificacao_autoridades' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $incidente = Incidente::create([
                'numero_incidente' => $this->generateNumeroIncidente(),
                'titulo' => $request->titulo,
                'descricao' => $request->descricao,
                'tipo' => $request->tipo,
                'gravidade' => $request->gravidade,
                'status' => 'aberto',
                'data_ocorrencia' => Carbon::parse($request->data_ocorrencia),
                'local_ocorrencia' => $request->local_ocorrencia,
                'embarcacao_id' => $request->embarcacao_id,
                'terminal_id' => $request->terminal_id,
                'pessoas_envolvidas' => $request->pessoas_envolvidas ?? [],
                'causas_identificadas' => $request->causas_identificadas,
                'acoes_imediatas' => $request->acoes_imediatas,
                'acoes_corretivas' => $request->acoes_corretivas,
                'requer_notificacao_autoridades' => $request->boolean('requer_notificacao_autoridades'),
                'user_id' => Auth::id(),
                'is_active' => true
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'created',
                'auditable_type' => 'Incidente',
                'auditable_id' => $incidente->id,
                'description' => "Incidente criado: {$incidente->numero_incidente} - {$incidente->titulo}",
                'new_values' => $incidente->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            // Enviar notificações por e-mail
            $this->enviarNotificacoesIncidente($incidente);

            DB::commit();

            Log::info('Incidente criado com sucesso', ['incidente_id' => $incidente->id, 'user_id' => Auth::id()]);

            return redirect()->route('incidentes.index')
                           ->with('success', 'Incidente registrado com sucesso! Notificações enviadas aos responsáveis.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar incidente: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao registrar incidente.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $incidente = Incidente::with(['user', 'embarcacao', 'terminal', 'responsavelInvestigacao'])->findOrFail($id);

            // Verificar permissão para concessionárias
            if (Auth::user()->hasRole('concessionaria') && $incidente->user_id !== Auth::id()) {
                abort(403, 'Acesso negado.');
            }

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'view',
                'auditable_type' => 'Incidente',
                'auditable_id' => $incidente->id,
                'description' => "Visualizou incidente: {$incidente->numero_incidente}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl()
            ]);

            return view('incidentes.show', compact('incidente'));

        } catch (\Exception $e) {
            Log::error('Erro ao visualizar incidente: ' . $e->getMessage());
            return back()->with('error', 'Incidente não encontrado.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $incidente = Incidente::findOrFail($id);

            // Verificar se o incidente pode ser editado
            if ($incidente->status === 'fechado') {
                return back()->with('error', 'Incidentes fechados não podem ser editados.');
            }

            $embarcacoes = Embarcacao::where('is_active', true)->get();
            $terminais = Terminal::where('is_active', true)->get();

            return view('incidentes.edit', compact('incidente', 'embarcacoes', 'terminais'));

        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição de incidente: ' . $e->getMessage());
            return back()->with('error', 'Incidente não encontrado.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string|max:2000',
            'tipo' => 'required|in:acidente,avaria,poluicao,seguranca,operacional,outros',
            'gravidade' => 'required|in:baixa,media,alta,critica',
            'data_ocorrencia' => 'required|date',
            'local_ocorrencia' => 'required|string|max:255',
            'embarcacao_id' => 'nullable|exists:embarcacoes,id',
            'terminal_id' => 'nullable|exists:terminais,id',
            'pessoas_envolvidas' => 'nullable|array',
            'pessoas_envolvidas.*.nome' => 'required_with:pessoas_envolvidas|string|max:255',
            'pessoas_envolvidas.*.cargo' => 'nullable|string|max:255',
            'pessoas_envolvidas.*.empresa' => 'nullable|string|max:255',
            'causas_identificadas' => 'nullable|string|max:2000',
            'acoes_imediatas' => 'nullable|string|max:2000',
            'acoes_corretivas' => 'nullable|string|max:2000',
            'requer_notificacao_autoridades' => 'boolean'
        ]);

        try {
            $incidente = Incidente::findOrFail($id);

            // Verificar se o incidente pode ser editado
            if ($incidente->status === 'fechado') {
                return back()->with('error', 'Incidentes fechados não podem ser editados.');
            }

            DB::beginTransaction();

            $dadosAntigos = $incidente->toArray();

            $incidente->update([
                'titulo' => $request->titulo,
                'descricao' => $request->descricao,
                'tipo' => $request->tipo,
                'gravidade' => $request->gravidade,
                'data_ocorrencia' => Carbon::parse($request->data_ocorrencia),
                'local_ocorrencia' => $request->local_ocorrencia,
                'embarcacao_id' => $request->embarcacao_id,
                'terminal_id' => $request->terminal_id,
                'pessoas_envolvidas' => $request->pessoas_envolvidas ?? [],
                'causas_identificadas' => $request->causas_identificadas,
                'acoes_imediatas' => $request->acoes_imediatas,
                'acoes_corretivas' => $request->acoes_corretivas,
                'requer_notificacao_autoridades' => $request->boolean('requer_notificacao_autoridades')
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'updated',
                'auditable_type' => 'Incidente',
                'auditable_id' => $incidente->id,
                'description' => "Incidente atualizado: {$incidente->numero_incidente}",
                'old_values' => $dadosAntigos,
                'new_values' => $incidente->fresh()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            Log::info('Incidente atualizado com sucesso', ['incidente_id' => $incidente->id, 'user_id' => Auth::id()]);

            return redirect()->route('incidentes.index')
                           ->with('success', 'Incidente atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar incidente: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao atualizar incidente.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $incidente = Incidente::findOrFail($id);

            // Verificar se pode ser excluído
            if ($incidente->status === 'investigando') {
                return back()->with('error', 'Incidentes em investigação não podem ser excluídos.');
            }

            DB::beginTransaction();

            // Log da ação antes de deletar
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'deleted',
                'auditable_type' => 'Incidente',
                'auditable_id' => $incidente->id,
                'description' => "Incidente excluído: {$incidente->numero_incidente}",
                'old_values' => $incidente->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl()
            ]);

            $incidente->delete();

            DB::commit();

            Log::info('Incidente excluído com sucesso', ['incidente_id' => $id, 'user_id' => Auth::id()]);

            return redirect()->route('incidentes.index')
                           ->with('success', 'Incidente excluído com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir incidente: ' . $e->getMessage());
            return back()->with('error', 'Erro ao excluir incidente.');
        }
    }

    /**
     * Iniciar investigação do incidente
     */
    public function investigate(Request $request, string $id)
    {
        $request->validate([
            'responsavel_investigacao' => 'required|exists:users,id'
        ]);

        try {
            $incidente = Incidente::findOrFail($id);

            if ($incidente->status !== 'aberto') {
                return back()->with('error', 'Apenas incidentes abertos podem entrar em investigação.');
            }

            DB::beginTransaction();

            $dadosAntigos = $incidente->toArray();

            $incidente->update([
                'status' => 'investigando',
                'responsavel_investigacao' => $request->responsavel_investigacao
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'investigate',
                'auditable_type' => 'Incidente',
                'auditable_id' => $incidente->id,
                'description' => "Investigação iniciada para incidente: {$incidente->numero_incidente}",
                'old_values' => $dadosAntigos,
                'new_values' => $incidente->fresh()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            Log::info('Investigação de incidente iniciada', ['incidente_id' => $incidente->id, 'user_id' => Auth::id()]);

            return back()->with('success', 'Investigação iniciada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao iniciar investigação: ' . $e->getMessage());
            return back()->with('error', 'Erro ao iniciar investigação.');
        }
    }

    /**
     * Fechar incidente
     */
    public function close(Request $request, string $id)
    {
        $request->validate([
            'causas_identificadas' => 'required|string|max:2000',
            'acoes_corretivas' => 'nullable|string|max:2000'
        ]);

        try {
            $incidente = Incidente::findOrFail($id);

            if ($incidente->status === 'fechado') {
                return back()->with('error', 'Incidente já foi fechado.');
            }

            DB::beginTransaction();

            $dadosAntigos = $incidente->toArray();

            $incidente->update([
                'status' => 'fechado',
                'data_fechamento' => now(),
                'causas_identificadas' => $request->causas_identificadas,
                'acoes_corretivas' => $request->acoes_corretivas
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'close',
                'auditable_type' => 'Incidente',
                'auditable_id' => $incidente->id,
                'description' => "Incidente fechado: {$incidente->numero_incidente}",
                'old_values' => $dadosAntigos,
                'new_values' => $incidente->fresh()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            Log::info('Incidente fechado com sucesso', ['incidente_id' => $incidente->id, 'user_id' => Auth::id()]);

            return back()->with('success', 'Incidente fechado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao fechar incidente: ' . $e->getMessage());
            return back()->with('error', 'Erro ao fechar incidente.');
        }
    }

    /**
     * Dashboard de incidentes
     */
    public function dashboard()
    {
        try {
            $query = Incidente::ativo();

            // Filtro por usuário se for concessionária
            if (Auth::user()->hasRole('concessionaria')) {
                $query->where('user_id', Auth::id());
            }

            $estatisticas = [
                'total' => $query->count(),
                'abertos' => $query->porStatus('aberto')->count(),
                'investigando' => $query->porStatus('investigando')->count(),
                'resolvidos' => $query->porStatus('resolvido')->count(),
                'fechados' => $query->porStatus('fechado')->count(),
                'criticos' => $query->porGravidade('critica')->count(),
                'mes_atual' => $query->whereMonth('data_ocorrencia', now()->month)
                                   ->whereYear('data_ocorrencia', now()->year)
                                   ->count()
            ];

            $incidentesPorTipo = $query->selectRaw('tipo, count(*) as total')
                                     ->groupBy('tipo')
                                     ->get();

            $incidentesPorGravidade = $query->selectRaw('gravidade, count(*) as total')
                                          ->groupBy('gravidade')
                                          ->get();

            $incidentesPorMes = $query->selectRaw('MONTH(data_ocorrencia) as mes, count(*) as total')
                                    ->whereYear('data_ocorrencia', now()->year)
                                    ->groupBy('mes')
                                    ->orderBy('mes')
                                    ->get();

            $incidentesRecentes = $query->with(['user', 'embarcacao', 'terminal'])
                                      ->orderBy('data_ocorrencia', 'desc')
                                      ->limit(10)
                                      ->get();

            return view('incidentes.dashboard', compact(
                'estatisticas',
                'incidentesPorTipo',
                'incidentesPorGravidade',
                'incidentesPorMes',
                'incidentesRecentes'
            ));

        } catch (\Exception $e) {
            Log::error('Erro ao carregar dashboard de incidentes: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar dashboard.');
        }
    }

    /**
     * Relatório de incidentes
     */
    public function relatorioFiltrado(Request $request)
    {
        try {
            $formato = $request->get('formato', 'view'); // Default para view
            
            $query = Incidente::with(['user', 'embarcacao', 'terminal', 'responsavelInvestigacao'])->ativo();

            // Aplicar os mesmos filtros da listagem
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('titulo', 'like', "%{$search}%")
                      ->orWhere('descricao', 'like', "%{$search}%")
                      ->orWhere('numero_incidente', 'like', "%{$search}%")
                      ->orWhere('local_ocorrencia', 'like', "%{$search}%");
                });
            }

            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            if ($request->filled('gravidade')) {
                $query->where('gravidade', $request->gravidade);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('data_inicio') && $request->filled('data_fim')) {
                $query->whereBetween('data_ocorrencia', [
                    Carbon::parse($request->data_inicio)->startOfDay(),
                    Carbon::parse($request->data_fim)->endOfDay()
                ]);
            }

            // Filtro por usuário se for concessionária
            if (Auth::user()->hasRole('concessionaria')) {
                $query->where('user_id', Auth::id());
            }

            $incidentes = $query->orderBy('data_ocorrencia', 'desc')->get();

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'export',
                'auditable_type' => 'App\\Models\\Incidente',
                'description' => "Gerou relatório de incidentes em formato: {$formato}",
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Gerar relatório baseado no formato
            if ($formato === 'pdf') {
                return $this->generatePdfReport($incidentes, $request);
            } elseif ($formato === 'excel') {
                return $this->generateExcelReport($incidentes, $request);
            }

            // View padrão (manter compatibilidade)
            $resumo = [
                'total_incidentes' => $incidentes->count(),
                'por_tipo' => $incidentes->groupBy('tipo')->map->count(),
                'por_gravidade' => $incidentes->groupBy('gravidade')->map->count(),
                'por_status' => $incidentes->groupBy('status')->map->count(),
                'pessoas_envolvidas_total' => $incidentes->sum(function($incidente) {
                    return is_array($incidente->pessoas_envolvidas) ? count($incidente->pessoas_envolvidas) : 0;
                })
            ];

            return view('incidentes.relatorio', compact('incidentes', 'resumo'));

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório de incidentes: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar relatório.');
        }
    }

    /**
     * Generate PDF report
     */
    private function generatePdfReport($incidentes, $request)
    {
        try {
            $configuracoes = config('relatorios.cabecalho_documentos', [
                'cabecalho' => [
                    'titulo' => 'PORTO DE SOYO',
                    'subtitulo' => 'Sistema Integrado de Gestão Portuária'
                ]
            ]);

            $filtros = [
                'search' => $request->get('search'),
                'tipo' => $request->get('tipo'),
                'gravidade' => $request->get('gravidade'),
                'status' => $request->get('status'),
                'data_inicio' => $request->get('data_inicio'),
                'data_fim' => $request->get('data_fim')
            ];

            $pdf = \PDF::loadView('incidentes.relatorio-pdf', compact('incidentes', 'filtros', 'configuracoes'));
            
            return $pdf->download('relatorio-incidentes-' . now()->format('Y-m-d-H-i-s') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF de incidentes: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate Excel report
     */
    private function generateExcelReport($incidentes, $request)
    {
        try {
            $filtrosAplicados = [];
            if ($request->get('search')) {
                $filtrosAplicados['Busca'] = $request->get('search');
            }
            if ($request->get('tipo')) {
                $filtrosAplicados['Tipo'] = $request->get('tipo');
            }
            if ($request->get('gravidade')) {
                $filtrosAplicados['Gravidade'] = $request->get('gravidade');
            }
            if ($request->get('status')) {
                $filtrosAplicados['Status'] = $request->get('status');
            }
            if ($request->get('data_inicio') && $request->get('data_fim')) {
                $filtrosAplicados['Período'] = $request->get('data_inicio') . ' a ' . $request->get('data_fim');
            }

            $filename = 'relatorio_incidentes_' . now()->format('Y-m-d_H-i-s') . '.xls';
            
            // Gerar conteúdo CSV compatível com Excel
            $csvContent = $this->generateExcelCsv($incidentes, $filtrosAplicados);
            
            // Headers para download compatível com Office 2013
            $headers = [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
                'Pragma' => 'public',
                'Expires' => '0'
            ];
            
            return response($csvContent, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar Excel de incidentes: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate CSV content compatible with Excel
     */
    private function generateExcelCsv($incidentes, $filtrosAplicados)
    {
        // BOM para UTF-8 (necessário para caracteres especiais no Excel)
        $csv = "\xEF\xBB\xBF";
        
        // Cabeçalho do relatório
        $csv .= "PORTO DE SOYO\n";
        $csv .= "Sistema Integrado de Gestão Portuária\n";
        $csv .= "Relatório de Incidentes\n\n";
        
        // Informações do relatório
        $csv .= "Data de Geração:;" . now()->format('d/m/Y H:i:s') . "\n";
        $csv .= "Total de Incidentes:;" . $incidentes->count() . "\n";
        $csv .= "Gerado por:;" . Auth::user()->name . "\n\n";
        
        // Filtros aplicados
        if (!empty($filtrosAplicados)) {
            $csv .= "Filtros Aplicados:\n";
            foreach ($filtrosAplicados as $filtro => $valor) {
                $csv .= $filtro . ":;" . $valor . "\n";
            }
            $csv .= "\n";
        }
        
        // Cabeçalhos da tabela
        $csv .= "Número;Título;Tipo;Gravidade;Status;Local;Data Ocorrência;Embarcação;Terminal;Responsável;Pessoas Envolvidas\n";
        
        // Dados dos incidentes
        foreach ($incidentes as $incidente) {
            $pessoasEnvolvidas = is_array($incidente->pessoas_envolvidas) ? count($incidente->pessoas_envolvidas) : 0;
            
            $csv .= sprintf(
                '"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";%d' . "\n",
                str_replace('"', '""', $incidente->numero_incidente ?? ''),
                str_replace('"', '""', $incidente->titulo ?? ''),
                str_replace('"', '""', ucfirst($incidente->tipo ?? '')),
                str_replace('"', '""', ucfirst($incidente->gravidade ?? '')),
                str_replace('"', '""', ucfirst($incidente->status ?? '')),
                str_replace('"', '""', $incidente->local_ocorrencia ?? ''),
                $incidente->data_ocorrencia ? $incidente->data_ocorrencia->format('d/m/Y H:i:s') : '',
                str_replace('"', '""', $incidente->embarcacao->nome ?? 'N/A'),
                str_replace('"', '""', $incidente->terminal->nome ?? 'N/A'),
                str_replace('"', '""', $incidente->user->name ?? 'N/A'),
                $pessoasEnvolvidas
            );
        }
        
        $csv .= "\n";
        $csv .= "Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária\n";
        
        return $csv;
    }

    /**
     * API endpoint para incidentes
     */
    public function api(Request $request)
    {
        try {
            $query = Incidente::with(['user', 'embarcacao', 'terminal', 'responsavelInvestigacao'])->ativo();

            // Filtro por usuário se for concessionária
            if (Auth::user()->hasRole('concessionaria')) {
                $query->where('user_id', Auth::id());
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('gravidade')) {
                $query->where('gravidade', $request->gravidade);
            }

            $incidentes = $query->orderBy('data_ocorrencia', 'desc')
                              ->paginate($request->get('per_page', 15));

            return response()->json($incidentes);

        } catch (\Exception $e) {
            Log::error('Erro na API de incidentes: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * Gerar número único do incidente
     */
    private function generateNumeroIncidente()
    {
        $ano = date('Y');
        $ultimoNumero = Incidente::whereYear('data_ocorrencia', $ano)
                                ->max('numero_incidente');

        if ($ultimoNumero) {
            $numero = intval(substr($ultimoNumero, -6)) + 1;
        } else {
            $numero = 1;
        }

        return 'INC' . $ano . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Exporta um incidente específico para PDF
     */
    public function exportPdf(Incidente $incidente)
    {
        try {
            // Carregar relacionamentos necessários
            $incidente->load(['user', 'embarcacao', 'terminal', 'responsavelInvestigacao']);

            // Buscar configurações do sistema para cabeçalho
            $configuracoes = \App\Models\Configuracao::whereIn('chave', [
                'cabecalho_documentos',
                'rodape_documentos',
                'logo_sistema'
            ])->pluck('valor', 'chave');

            // Buscar logs de auditoria relacionados ao incidente
            $auditLogs = AuditLog::where('auditable_type', 'App\Models\Incidente')
                                ->where('auditable_id', $incidente->id)
                                ->orderBy('created_at', 'desc')
                                ->get();

            // Registrar log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'auditable_type' => 'App\Models\Incidente',
                'auditable_id' => $incidente->id,
                'event' => 'export_pdf',
                'old_values' => null,
                'new_values' => null,
                'description' => "Exportou PDF do incidente: {$incidente->numero_incidente}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Gerar PDF usando DomPDF
            $pdf = Pdf::loadView('incidentes.pdf', compact('incidente', 'configuracoes', 'auditLogs'));

            // Configurações do PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial',
                'isRemoteEnabled' => true
            ]);

            $filename = 'incidente_' . $incidente->numero_incidente . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('IncidenteController@exportPdf: Erro ao gerar PDF', [
                'incidente_id' => $incidente->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erro ao gerar PDF: ' . $e->getMessage());
        }
    }

    /**
     * Enviar notificações por e-mail para os responsáveis
     */
    private function enviarNotificacoesIncidente(Incidente $incidente)
    {
        try {
            Log::info("Iniciando envio de notificações para incidente: {$incidente->numero_incidente}");
            
            // Buscar usuários que devem receber notificações
            $usuarios = User::where('is_active', true)
                           ->whereHas('roles', function($query) {
                               $query->whereIn('name', ['admin', 'supervisor_portuario', 'inspector_isps']);
                           })
                           ->get();

            Log::info("Encontrados {$usuarios->count()} usuários para notificar sobre incidente");

            if ($usuarios->isEmpty()) {
                Log::warning("Nenhum usuário encontrado para receber notificações de incidente");
                return;
            }

            $emailsEnviados = 0;
            
            foreach ($usuarios as $usuario) {
                try {
                    Log::info("Enviando notificação de incidente para usuário: {$usuario->name} (ID: {$usuario->id})");
                    
                    // Carregar relacionamentos do incidente para o e-mail
                    $incidente->load(['user', 'embarcacao', 'terminal']);
                    
                    // Enviar e-mail
                    Mail::to($usuario->email)->send(new NovoIncidenteNotification($incidente));
                    
                    $emailsEnviados++;
                    Log::info("E-mail de incidente enviado com sucesso para: {$usuario->email}");
                    
                } catch (\Exception $e) {
                    Log::error("Erro ao enviar e-mail de incidente para {$usuario->email}: " . $e->getMessage());
                }
            }

            Log::info("Notificações de incidente enviadas: {$emailsEnviados} de {$usuarios->count()}");
            
        } catch (\Exception $e) {
            Log::error('Erro geral ao enviar notificações de incidente: ' . $e->getMessage());
        }
    }
}