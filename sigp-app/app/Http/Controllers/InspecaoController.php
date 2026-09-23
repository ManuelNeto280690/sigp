<?php

namespace App\Http\Controllers;

use App\Models\Inspecao;
use App\Models\AuditLog;
use App\Models\Embarcacao;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Terminal;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Facades\Config;

class InspecaoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('role:admin|inspector_cais')->except(['index', 'show', 'api']);
        $this->middleware('role:admin|inspector_cais')->only(['create', 'store', 'edit', 'update', 'destroy', 'approve', 'reject']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Inspecao::with(['embarcacao', 'inspetor', 'aprovadoPor']);

            // Filtros
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('numero_inspecao', 'like', "%{$search}%")
                      ->orWhere('observacoes', 'like', "%{$search}%")
                      ->orWhereHas('embarcacao', function($eq) use ($search) {
                          $eq->where('nome', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->filled('tipo_inspecao')) {
                $query->where('tipo_inspecao', $request->tipo_inspecao);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('resultado')) {
                $query->where('resultado', $request->resultado);
            }

            if ($request->filled('data_inicio') && $request->filled('data_fim')) {
                $query->whereBetween('data_inspecao', [
                    Carbon::parse($request->data_inicio)->startOfDay(),
                    Carbon::parse($request->data_fim)->endOfDay()
                ]);
            }

            if ($request->filled('embarcacao_id')) {
                $query->where('embarcacao_id', $request->embarcacao_id);
            }

            // Filtro por inspetor para usuários com role inspector_cais
            if (Auth::user()->hasRole('inspector_cais')) {
                $query->where('inspetor_id', Auth::id());
            }

            $inspecoes = $query->where('is_active', true)
                              ->orderBy('data_inspecao', 'desc')
                              ->paginate(15);

            $embarcacoes = Embarcacao::where('is_active', true)->orderBy('nome')->get();

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'view',
                'auditable_type' => 'Inspecao',
                'auditable_id' => null,
                'description' => 'Visualizou listagem de inspeções',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            return view('inspecoes.index', compact('inspecoes', 'embarcacoes'));

        } catch (\Exception $e) {
            Log::error('Erro ao listar inspeções: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar inspeções.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $embarcacoes = Embarcacao::where('is_active', true)->orderBy('nome')->get();
            $inspetores = User::role('inspector_cais')->orderBy('name')->get();
            $terminais = Terminal::where('is_active', true)->get();

            return view('inspecoes.create', compact('embarcacoes', 'inspetores', 'terminais'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de criação de inspeção: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'embarcacao_id' => 'required|exists:embarcacoes,id',
            'inspetor_id' => 'required|exists:users,id',
            'tipo_inspecao' => 'required|string',
            'data_inspecao' => 'required|date',
           // 'hora_inicio' => 'required|date_format:H:i',
            'hora_fim' => 'nullable|date_format:H:i|after:hora_inicio',
            'checklist_itens' => 'nullable|array',
            'nao_conformidades' => 'nullable|array',
            'observacoes' => 'nullable|string|max:2000',
           // 'documentos_verificados' => 'nullable|array',
           // 'evidencias' => 'nullable|array',
            'restricoes' => 'nullable|string|max:1000',
            'acoes_corretivas' => 'nullable|string|max:2000',
            'prazo_correcao' => 'nullable|date|after:today'
        ]);

        try {
            DB::beginTransaction();

            $dataInspecao = Carbon::parse($request->data_inspecao);
           // $horaInicio = $dataInspecao->copy()->setTimeFromTimeString($request->hora_inicio);
         //   $horaFim = $request->hora_fim ? $dataInspecao->copy()->setTimeFromTimeString($request->hora_fim) : null;

            $inspecao = Inspecao::create([
                'numero_inspecao' => $this->generateNumeroInspecao(),
                'embarcacao_id' => $request->embarcacao_id,
                'inspetor_id' => $request->inspetor_id,
                'tipo_inspecao' => $request->tipo_inspecao,
                'data_inspecao' => $dataInspecao,
               // 'hora_inicio' => $horaInicio,
               // 'hora_fim' => $horaFim,
                'checklist_itens' => $request->checklist_itens ?? [],
                'nao_conformidades' => $request->nao_conformidades ?? [],
                'observacoes' => $request->observacoes,
                'restricoes' => $request->restricoes,
                'acoes_corretivas' => $request->acoes_corretivas,
                'prazo_correcao' => $request->prazo_correcao ? Carbon::parse($request->prazo_correcao) : null,
                'documentos_verificados' => $request->documentos_verificados ?? [],
                'evidencias' => $request->evidencias ?? [],
                'status' => 'agendada'
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'created',
                'auditable_type' => 'Inspecao',
                'auditable_id' => $inspecao->id,
                'description' => "Inspeção criada: {$inspecao->numero_inspecao}",
                'new_values' => $inspecao->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            Log::info('Inspeção criada com sucesso', ['inspecao_id' => $inspecao->id, 'user_id' => Auth::id()]);

            return redirect()->route('inspecoes.index')
                           ->with('success', 'Inspeção agendada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar inspeção: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao agendar inspeção.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $inspecao = Inspecao::with(['embarcacao', 'inspetor', 'aprovadoPor'])->findOrFail($id);

            // Verificar permissão para inspetores
            if (Auth::user()->hasRole('inspector_cais') && $inspecao->inspetor_id !== Auth::id()) {
                abort(403, 'Acesso negado.');
            }

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'view',
                'auditable_type' => 'Inspecao',
                'auditable_id' => $inspecao->id,
                'description' => "Visualizou inspeção: {$inspecao->numero_inspecao}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl()
            ]);

            return view('inspecoes.show', compact('inspecao'));

        } catch (\Exception $e) {
            Log::error('Erro ao visualizar inspeção: ' . $e->getMessage());
            return back()->with('error', 'Inspeção não encontrada.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $inspecao = Inspecao::findOrFail($id);

            // Verificar se pode ser editada
            if (in_array($inspecao->status, ['concluida', 'aprovada'])) {
                return back()->with('error', 'Inspeções concluídas ou aprovadas não podem ser editadas.');
            }

             $embarcacoes = Embarcacao::where('is_active', true)->orderBy('nome')->get();
            $inspetores = User::role('inspector_cais')->orderBy('name')->get();
            $terminais = Terminal::where('is_active', true)->get();

            return view('inspecoes.edit', compact('inspecao', 'embarcacoes', 'inspetores', 'terminais'));

        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar formulário de edição.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'embarcacao_id' => 'required|exists:embarcacoes,id',
            'inspetor_id' => 'required|exists:users,id',
            'tipo_inspecao' => 'required|in:seguranca,ambiental,documentacao,estrutural,equipamentos',
            'data_inspecao' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fim' => 'nullable|date_format:H:i|after:hora_inicio',
            'checklist_itens' => 'nullable|array',
            'nao_conformidades' => 'nullable|array',
            'observacoes' => 'nullable|string|max:2000',
            'resultado' => 'nullable|in:aprovado,reprovado,condicional',
            'restricoes' => 'nullable|string|max:1000',
            'acoes_corretivas' => 'nullable|string|max:2000',
            'prazo_correcao' => 'nullable|date|after:today',
            'documentos_verificados' => 'nullable|array',
            'evidencias' => 'nullable|array'
        ]);

        try {
            $inspecao = Inspecao::findOrFail($id);

            // Verificar se pode ser editada
            if (in_array($inspecao->status, ['concluida', 'aprovada'])) {
                return back()->with('error', 'Inspeções concluídas ou aprovadas não podem ser editadas.');
            }

            DB::beginTransaction();

            $dadosAntigos = $inspecao->toArray();

            $dataInspecao = Carbon::parse($request->data_inspecao);
            $horaInicio = $dataInspecao->copy()->setTimeFromTimeString($request->hora_inicio);
            $horaFim = $request->hora_fim ? $dataInspecao->copy()->setTimeFromTimeString($request->hora_fim) : null;

            $inspecao->update([
                'embarcacao_id' => $request->embarcacao_id,
                'inspetor_id' => $request->inspetor_id,
                'tipo_inspecao' => $request->tipo_inspecao,
                'data_inspecao' => $dataInspecao,
                'hora_inicio' => $horaInicio,
                'hora_fim' => $horaFim,
                'checklist_itens' => $request->checklist_itens ?? [],
                'nao_conformidades' => $request->nao_conformidades ?? [],
                'observacoes' => $request->observacoes,
                'resultado' => $request->resultado,
                'restricoes' => $request->restricoes,
                'acoes_corretivas' => $request->acoes_corretivas,
                'prazo_correcao' => $request->prazo_correcao ? Carbon::parse($request->prazo_correcao) : null,
                'documentos_verificados' => $request->documentos_verificados ?? [],
                'evidencias' => $request->evidencias ?? []
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'updated',
                'auditable_type' => 'Inspecao',
                'auditable_id' => $inspecao->id,
                'description' => "Inspeção atualizada: {$inspecao->numero_inspecao}",
                'old_values' => $dadosAntigos,
                'new_values' => $inspecao->fresh()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            Log::info('Inspeção atualizada com sucesso', ['inspecao_id' => $inspecao->id, 'user_id' => Auth::id()]);

            return redirect()->route('inspecoes.index')
                           ->with('success', 'Inspeção atualizada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar inspeção: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao atualizar inspeção.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $inspecao = Inspecao::findOrFail($id);

            // Verificar se pode ser excluída
            if (in_array($inspecao->status, ['em_andamento', 'concluida', 'aprovada'])) {
                return back()->with('error', 'Inspeções em andamento, concluídas ou aprovadas não podem ser excluídas.');
            }

            DB::beginTransaction();

            // Log da ação antes de deletar
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'deleted',
                'auditable_type' => 'Inspecao',
                'auditable_id' => $inspecao->id,
                'description' => "Inspeção excluída: {$inspecao->numero_inspecao}",
                'old_values' => $inspecao->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl()
            ]);

            $inspecao->delete();

            DB::commit();

            Log::info('Inspeção excluída com sucesso', ['inspecao_id' => $id, 'user_id' => Auth::id()]);

            return redirect()->route('inspecoes.index')
                           ->with('success', 'Inspeção excluída com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir inspeção: ' . $e->getMessage());
            return back()->with('error', 'Erro ao excluir inspeção.');
        }
    }

    /**
     * Iniciar inspeção
     */
    public function start(string $id)
    {
        try {
            $inspecao = Inspecao::findOrFail($id);

            if ($inspecao->status !== 'agendada') {
                return back()->with('error', 'Apenas inspeções agendadas podem ser iniciadas.');
            }

            DB::beginTransaction();

            $dadosAntigos = $inspecao->toArray();

            $inspecao->update([
                'status' => 'em_andamento',
                'hora_inicio' => now()
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'start',
                'auditable_type' => 'Inspecao',
                'auditable_id' => $inspecao->id,
                'description' => "Inspeção iniciada: {$inspecao->numero_inspecao}",
                'old_values' => $dadosAntigos,
                'new_values' => $inspecao->fresh()->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl()
            ]);

            DB::commit();

            Log::info('Inspeção iniciada com sucesso', ['inspecao_id' => $inspecao->id, 'user_id' => Auth::id()]);

            return back()->with('success', 'Inspeção iniciada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao iniciar inspeção: ' . $e->getMessage());
            return back()->with('error', 'Erro ao iniciar inspeção.');
        }
    }

    /**
     * Concluir inspeção
     */
    public function complete(Request $request, string $id)
    {
        $request->validate([
            'resultado' => 'required|in:aprovado,reprovado,condicional',
            'nao_conformidades' => 'nullable|array',
            'acoes_corretivas' => 'nullable|string|max:2000',
            'prazo_correcao' => 'nullable|date|after:today'
        ]);

        try {
            $inspecao = Inspecao::findOrFail($id);

            if ($inspecao->status !== 'em_andamento') {
                return back()->with('error', 'Apenas inspeções em andamento podem ser concluídas.');
            }

            DB::beginTransaction();

            $dadosAntigos = $inspecao->toArray();

            $inspecao->update([
                'status' => 'concluida',
                'hora_fim' => now(),
                'resultado' => $request->resultado,
                'nao_conformidades' => $request->nao_conformidades ?? [],
                'acoes_corretivas' => $request->acoes_corretivas,
                'prazo_correcao' => $request->prazo_correcao ? Carbon::parse($request->prazo_correcao) : null
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'complete',
                'auditable_type' => 'Inspecao',
                'auditable_id' => $inspecao->id,
                'description' => "Inspeção concluída: {$inspecao->numero_inspecao} - Resultado: {$request->resultado}",
                'old_values' => $dadosAntigos,
                'new_values' => $inspecao->fresh()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            Log::info('Inspeção concluída com sucesso', ['inspecao_id' => $inspecao->id, 'user_id' => Auth::id()]);

            return back()->with('success', 'Inspeção concluída com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao concluir inspeção: ' . $e->getMessage());
            return back()->with('error', 'Erro ao concluir inspeção.');
        }
    }

    /**
     * Aprovar inspeção
     */
    public function approve(string $id)
    {
        try {
            $inspecao = Inspecao::findOrFail($id);

            if ($inspecao->status !== 'concluida') {
                return back()->with('error', 'Apenas inspeções concluídas podem ser aprovadas.');
            }

            DB::beginTransaction();

            $dadosAntigos = $inspecao->toArray();

            $inspecao->update([
                'status' => 'aprovada',
                'aprovado_por' => Auth::id(),
                'aprovado_em' => now()
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'approve',
                'auditable_type' => 'Inspecao',
                'auditable_id' => $inspecao->id,
                'description' => "Inspeção aprovada: {$inspecao->numero_inspecao}",
                'old_values' => $dadosAntigos,
                'new_values' => $inspecao->fresh()->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl()
            ]);

            DB::commit();

            Log::info('Inspeção aprovada com sucesso', ['inspecao_id' => $inspecao->id, 'user_id' => Auth::id()]);

            return back()->with('success', 'Inspeção aprovada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao aprovar inspeção: ' . $e->getMessage());
            return back()->with('error', 'Erro ao aprovar inspeção.');
        }
    }

    /**
     * Gerar número único para inspeção
     */
    private function generateNumeroInspecao(): string
    {
        $ano = date('Y');
        $ultimaInspecao = Inspecao::whereYear('created_at', $ano)
                                 ->orderBy('created_at', 'desc')
                                 ->first();

        $proximoNumero = $ultimaInspecao ? 
            intval(substr($ultimaInspecao->numero_inspecao, -4)) + 1 : 1;

        return 'INSP-' . $ano . '-' . str_pad($proximoNumero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate environmental inspections report
     */
    public function relatorioAmbiental(Request $request)
    {
        $dataInicio = $request->get('data_inicio', now()->subMonth()->format('Y-m-d'));
        $dataFim = $request->get('data_fim', now()->format('Y-m-d'));
        $formato = $request->get('formato', 'html');

        $query = Inspecao::with(['embarcacao', 'inspetor', 'aprovadoPor'])
            ->ambientais() // Usar o scope para inspeções ambientais
            ->whereBetween('created_at', [$dataInicio, $dataFim]);

        // Filtros adicionais
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('resultado')) {
            $query->where('resultado', $request->resultado);
        }

        if ($request->filled('embarcacao_id')) {
            $query->where('embarcacao_id', $request->embarcacao_id);
        }

        $inspecoes = $query->orderBy('created_at', 'desc')->get();

        // Estatísticas específicas para inspeções ambientais
        $estatisticas = [
            'total' => $inspecoes->count(),
            'concluidas' => $inspecoes->where('status', 'concluída')->count(),
            'em_andamento' => $inspecoes->where('status', 'em andamento')->count(),
            'pendentes' => $inspecoes->where('status', 'pendente')->count(),
            'aprovadas' => $inspecoes->where('status', 'aprovada')->count(),
            'reprovadas' => $inspecoes->where('resultado', 'reprovado')->count(),
            'com_restricoes' => $inspecoes->where('resultado', 'aprovado_com_restricoes')->count(),
            'com_infracoes' => $inspecoes->filter(function($inspecao) {
                return !empty($inspecao->nao_conformidades);
            })->count(),
            'por_resultado' => $inspecoes->groupBy('resultado')->map->count(),
            'por_mes' => $inspecoes->groupBy(function($item) {
                return $item->created_at->format('Y-m');
            })->map->count()
        ];

        $dados = [
            'inspecoes' => $inspecoes,
            'estatisticas' => $estatisticas,
            'periodo' => [
                'inicio' => $dataInicio,
                'fim' => $dataFim
            ],
            'filtros' => $request->all()
        ];

        // Log da ação
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'report_generated',
            'auditable_type' => 'Inspecao',
            'auditable_id' => null,
            'description' => "Relatório de inspeções ambientais gerado - Período: {$dataInicio} a {$dataFim}",
            'new_values' => [
                'tipo' => 'inspecoes_ambientais',
                'periodo' => $dataInicio . ' a ' . $dataFim,
                'total_registros' => $inspecoes->count()
            ]
        ]);

        if ($formato !== 'html') {
            return $this->exportRelatorio($dados, $formato);
        }

        return view('inspecoes.relatorio-ambiental', $dados);
    }

    /**
     * Export inspections report in different formats
     */
    private function exportRelatorio($dados, $formato)
    {
        switch ($formato) {
            case 'pdf':
                // Implementar exportação PDF
                return response()->json([
                    'message' => 'Exportação PDF em desenvolvimento',
                    'dados' => $dados
                ]);
            case 'excel':
                // Implementar exportação Excel
                return response()->json([
                    'message' => 'Exportação Excel em desenvolvimento',
                    'dados' => $dados
                ]);
            case 'csv':
                // Implementar exportação CSV
                $csv = $this->generateCSV($dados['inspecoes']);
                return response($csv)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="relatorio_inspecoes.csv"');
            default:
                return response()->json([
                    'message' => 'Formato não suportado',
                    'dados' => $dados
                ]);
        }
    }

    /**
     * Generate CSV content for inspections
     */
    private function generateCSV($inspecoes)
    {
        $csv = "Número,Data,Tipo,Status,Resultado,Embarcação,Inspetor,Local,Observações\n";
        
        foreach ($inspecoes as $inspecao) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $inspecao->numero_inspecao,
                $inspecao->created_at->format('d/m/Y H:i'),
                $inspecao->tipo_inspecao,
                $inspecao->status,
                $inspecao->resultado ?? 'N/A',
                $inspecao->embarcacao->nome ?? 'N/A',
                $inspecao->inspetor->name ?? 'N/A',
                $inspecao->local_inspecao,
                str_replace('"', '""', $inspecao->observacoes ?? '')
            );
        }
        
        return $csv;
    }

    /**
     * Generate report with filtered inspections (PDF or Excel)
     */
    public function relatorioFiltrado(Request $request)
    {
        try {
            $formato = $request->get('formato', 'pdf'); // Default para PDF
            
            // Aplicar os mesmos filtros do método index
            $query = Inspecao::with(['embarcacao', 'inspetor', 'aprovadoPor']);

            // Filtro de busca
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('numero_inspecao', 'like', "%{$search}%")
                      ->orWhere('observacoes', 'like', "%{$search}%")
                      ->orWhereHas('embarcacao', function($eq) use ($search) {
                          $eq->where('nome', 'like', "%{$search}%");
                      });
                });
            }

            // Filtros específicos
            if ($request->filled('tipo_inspecao')) {
                $query->where('tipo_inspecao', $request->tipo_inspecao);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('resultado')) {
                $query->where('resultado', $request->resultado);
            }

            if ($request->filled('data_inicio') && $request->filled('data_fim')) {
                $query->whereBetween('data_inspecao', [
                    Carbon::parse($request->data_inicio)->startOfDay(),
                    Carbon::parse($request->data_fim)->endOfDay()
                ]);
            }

            if ($request->filled('embarcacao_id')) {
                $query->where('embarcacao_id', $request->embarcacao_id);
            }

            // Filtro por inspetor para usuários com role inspector_cais
            if (Auth::user()->hasRole('inspector_cais')) {
                $query->where('inspetor_id', Auth::id());
            }

            $inspecoes = $query->where('is_active', true)
                              ->orderBy('data_inspecao', 'desc')
                              ->get();

            // Estatísticas resumidas
            $estatisticas = [
                'total' => $inspecoes->count(),
                'agendadas' => $inspecoes->where('status', 'agendada')->count(),
                'em_andamento' => $inspecoes->where('status', 'em_andamento')->count(),
                'concluidas' => $inspecoes->where('status', 'concluida')->count(),
                'canceladas' => $inspecoes->where('status', 'cancelada')->count(),
                'aprovadas' => $inspecoes->where('resultado', 'aprovada')->count(),
                'reprovadas' => $inspecoes->where('resultado', 'reprovada')->count(),
                'pendentes' => $inspecoes->where('resultado', 'pendente')->count(),
            ];

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'report_generated',
                'auditable_type' => 'Inspecao',
                'auditable_id' => null,
                'description' => 'Relatório de inspeções gerado',
                'new_values' => [
                    'formato' => $formato,
                    'filtros' => $request->all(),
                    'total_registros' => $inspecoes->count()
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            if ($formato === 'excel') {
                return $this->generateExcelReport($inspecoes, $request->all(), $estatisticas);
            } else {
                return $this->generatePdfReport($inspecoes, $request->all(), $estatisticas);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório de inspeções: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF report for inspections
     */
    private function generatePdfReport($inspecoes, $filtros, $estatisticas)
    {
        try {
            // Obter configurações do sistema
            $cabecalho_documentos = Config::get('cabecalho_documentos', 'SISTEMA DE GESTÃO PORTUÁRIA');
            
            // Se cabecalho_documentos for array, extrair o título
            if (is_array($cabecalho_documentos)) {
                $cabecalho_documentos = $cabecalho_documentos['titulo'] ?? 'SISTEMA DE GESTÃO PORTUÁRIA';
            }
            
            $data = [
                'inspecoes' => $inspecoes,
                'filtros' => $filtros,
                'estatisticas' => $estatisticas,
                'cabecalho_documentos' => $cabecalho_documentos,
                'data_geracao' => now()->format('d/m/Y H:i:s'),
                'usuario' => Auth::user()->name
            ];

            $pdf = Pdf::loadView('inspecoes.relatorio-pdf', $data);
            $pdf->setPaper('A4', 'landscape');
            
            return $pdf->download('relatorio_inspecoes_' . now()->format('Y-m-d_H-i-s') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF de inspeções: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate Excel report for inspections
     */
    private function generateExcelReport($inspecoes, $filtros, $estatisticas)
    {
        try {
            // Obter configurações do sistema
            $cabecalho_documentos = Config::get('cabecalho_documentos', 'SISTEMA DE GESTÃO PORTUÁRIA');
            
            // Se cabecalho_documentos for array, extrair o título
            if (is_array($cabecalho_documentos)) {
                $cabecalho_documentos = $cabecalho_documentos['titulo'] ?? 'SISTEMA DE GESTÃO PORTUÁRIA';
            }
            
            $csv = "\xEF\xBB\xBF"; // BOM para UTF-8
            $csv .= "{$cabecalho_documentos}\n";
            $csv .= "RELATÓRIO DE INSPEÇÕES DE NAVIOS\n";
            $csv .= "Data de Geração: " . now()->format('d/m/Y H:i:s') . "\n";
            $csv .= "Usuário: " . Auth::user()->name . "\n\n";

            // Filtros aplicados
            $csv .= "FILTROS APLICADOS:\n";
            if (!empty($filtros['search'])) {
                $csv .= "Busca: {$filtros['search']}\n";
            }
            if (!empty($filtros['tipo_inspecao'])) {
                $csv .= "Tipo: {$filtros['tipo_inspecao']}\n";
            }
            if (!empty($filtros['status'])) {
                $csv .= "Status: {$filtros['status']}\n";
            }
            if (!empty($filtros['resultado'])) {
                $csv .= "Resultado: {$filtros['resultado']}\n";
            }
            if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                $csv .= "Período: {$filtros['data_inicio']} a {$filtros['data_fim']}\n";
            }
            $csv .= "\n";

            // Estatísticas resumidas
            $csv .= "RESUMO ESTATÍSTICO:\n";
            $csv .= "Total de Inspeções: {$estatisticas['total']}\n";
            $csv .= "Agendadas: {$estatisticas['agendadas']}\n";
            $csv .= "Em Andamento: {$estatisticas['em_andamento']}\n";
            $csv .= "Concluídas: {$estatisticas['concluidas']}\n";
            $csv .= "Canceladas: {$estatisticas['canceladas']}\n";
            $csv .= "Aprovadas: {$estatisticas['aprovadas']}\n";
            $csv .= "Reprovadas: {$estatisticas['reprovadas']}\n";
            $csv .= "Pendentes: {$estatisticas['pendentes']}\n\n";

            // Cabeçalho da tabela
            $csv .= "DADOS DETALHADOS:\n";
            $csv .= "Número,Data Inspeção,Tipo,Status,Resultado,Embarcação,Inspetor,Local,Observações\n";

            // Dados das inspeções
            foreach ($inspecoes as $inspecao) {
                $csv .= sprintf(
                    '"%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                    $inspecao->numero_inspecao ?? 'N/A',
                    $inspecao->data_inspecao ? Carbon::parse($inspecao->data_inspecao)->format('d/m/Y H:i') : 'N/A',
                    $inspecao->tipo_inspecao ?? 'N/A',
                    $inspecao->status ?? 'N/A',
                    $inspecao->resultado ?? 'N/A',
                    $inspecao->embarcacao->nome ?? 'N/A',
                    $inspecao->inspetor->name ?? 'N/A',
                    $inspecao->local_inspecao ?? 'N/A',
                    str_replace('"', '""', $inspecao->observacoes ?? '')
                );
            }

            return response($csv)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="relatorio_inspecoes_' . now()->format('Y-m-d_H-i-s') . '.csv"');

        } catch (\Exception $e) {
            Log::error('Erro ao gerar Excel de inspeções: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export inspeção to PDF
     */
    public function exportPdf(Inspecao $inspecao, Request $request)
    {
        try {
            // Carregar relacionamentos necessários
            $inspecao->load(['embarcacao', 'inspetor', 'aprovadoPor']);

            // Buscar configurações do sistema para cabeçalho e rodapé
            $configuracoes = [
                'nome_sistema' => config('app.name', 'Sistema Integrado de Gestão Portuária'),
                'nome_porto' => 'PORTO DE SOYO',
                'logotipo' => asset('images/logo.png'),
                'endereco' => 'Soyo, Província do Zaire, Angola',
                'telefone' => '+244 XXX XXX XXX',
                'email' => 'contato@portodesoyo.ao',
                'rodape' => 'Porto de Soyo - Sistema Integrado de Gestão Portuária'
            ];

            // Buscar logs de auditoria relacionados à inspeção
            $auditLogs = AuditLog::where('auditable_type', 'Inspecao')
                ->where('auditable_id', $inspecao->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'pdf_exported',
                'auditable_type' => 'Inspecao',
                'auditable_id' => $inspecao->id,
                'description' => "PDF da inspeção exportado: {$inspecao->numero_inspecao}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            // Gerar PDF
            $pdf = Pdf::loadView('inspecoes.pdf', compact('inspecao', 'configuracoes', 'auditLogs'));
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial'
            ]);

            $filename = 'inspecao_navio_' . $inspecao->numero_inspecao . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF da inspeção', [
                'inspecao_id' => $inspecao->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erro ao gerar PDF: ' . $e->getMessage());
        }
    }
}