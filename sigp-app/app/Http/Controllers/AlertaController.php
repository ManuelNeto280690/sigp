<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AlertaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('role:admin|inspector_isps')->except(['index', 'show', 'api']);
        $this->middleware('role:admin|inspector_isps')->only(['create', 'store', 'edit', 'update', 'destroy', 'resolve', 'dismiss']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Log da ação
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'view',
            'auditable_type' => 'App\\Models\\Alerta',
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    
        $query = Alerta::with(['user', 'resolvidoPor']);
    
        // Busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%");
            });
        }
    
        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        if ($request->filled('nivel')) {
            $query->where('nivel', $request->nivel);
        }
    
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
    
        // Ordenação
        $alertas = $query->orderBy('nivel', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);
    
        // Estatísticas
        $estatisticas = [
            'total' => Alerta::count(),
            'pendentes' => Alerta::where('status', 'ativo')->count(),
            'resolvidos' => Alerta::where('status', 'resolvido')->count(),
            'criticos' => Alerta::where('nivel', 'critica')->where('status', 'ativo')->count(),
        ];
    
        return view('alertas.index', compact('alertas', 'estatisticas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('alertas.create');
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de criação de alerta: ' . $e->getMessage());
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
            'descricao' => 'required|string',
            'tipo' => 'required|string',
            'nivel' => 'required|string',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after:data_inicio',
            'areas_afetadas' => 'nullable|string',
            'acoes_tomadas' => 'nullable|string',
            'notificar_usuarios' => 'nullable|boolean'
        ]);
    
        try {
            DB::beginTransaction();
    
            $alerta = Alerta::create([
                'id' => Str::uuid(),
                'titulo' => $request->titulo,
                'descricao' => $request->descricao,
                'tipo' => $request->tipo,
                'nivel' => $request->nivel,
                'status' => 'activo',
                'data_inicio' => $request->data_inicio,
                'data_fim' => $request->data_fim,
                'areas_afetadas' => $request->areas_afetadas,
                'acoes_tomadas' => $request->acoes_tomadas,
                'notificar_usuarios' => $request->boolean('notificar_usuarios', false),
                'is_active' => true,
                'user_id' => Auth::id()
            ]);
    
            // Enviar notificações se solicitado
            if ($request->boolean('notificar_usuarios', false)) {
                $this->enviarNotificacoes($alerta);
            }
    
            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'create',
                'auditable_type' => 'App\\Models\\Alerta',
                'auditable_id' => $alerta->id,
                'new_values' => json_encode($alerta->toArray()),
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
    
            DB::commit();
    
            return redirect()->route('alertas.index')
                            ->with('success', 'Alerta criado com sucesso!');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar alerta: ' . $e->getMessage());
            return back()->withInput()
                        ->with('error', 'Erro ao criar alerta. Tente novamente.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        try {
            $alerta = Alerta::with(['user'])->findOrFail($id);

          

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'view',
                'auditable_type' => 'App\\Models\\Alerta',
                'auditable_id' => null,
                'url' => $request->url(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return view('alertas.show', compact('alerta'));

        } catch (\Exception $e) {
            Log::error('Erro ao visualizar alerta: ' . $e->getMessage());
            return back()->with('error', 'Alerta não encontrado.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $alerta = Alerta::findOrFail($id);

            // Verificar se o alerta pode ser editado
            if ($alerta->status === 'resolvido') {
                return back()->with('error', 'Alertas resolvidos não podem ser editados.');
            }

            return view('alertas.edit', compact('alerta'));

        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição de alerta: ' . $e->getMessage());
            return back()->with('error', 'Alerta não encontrado.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'tipo' => 'required|string',
            'nivel' => 'required|string',
            'status' => 'string',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after:data_inicio',
            'areas_afetadas' => 'nullable|string',
            'acoes_tomadas' => 'nullable|string',
            'notificar_usuarios' => 'nullable|boolean'
        ]);

        try {
            $alerta = Alerta::findOrFail($id);

            // Verificar se o alerta pode ser editado
            if ($alerta->status === 'resolvido') {
                return back()->with('error', 'Alertas resolvidos não podem ser editados.');
            }

            DB::beginTransaction();

            $dadosAntigos = $alerta->toArray();

            // Processar areas_afetadas - converter string em array se necessário
            $areasAfetadas = $request->areas_afetadas;
            if (is_string($areasAfetadas) && !empty($areasAfetadas)) {
                $areasAfetadas = array_filter(array_map('trim', explode("\n", $areasAfetadas)));
            } elseif (empty($areasAfetadas)) {
                $areasAfetadas = null;
            }

            $alerta->update([
                'titulo' => $request->titulo,
                'descricao' => $request->descricao,
                'tipo' => $request->tipo,
                'nivel' => $request->nivel,
                'status' => $request->status ?? $alerta->status,
                'data_inicio' => $request->data_inicio,
                'data_fim' => $request->data_fim,
                'areas_afetadas' => $areasAfetadas,
                'acoes_tomadas' => $request->acoes_tomadas,
                'notificar_usuarios' => $request->boolean('notificar_usuarios', $alerta->notificar_usuarios)
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'App\\Models\\Alerta',
                'auditable_id' => $alerta->id,
                'old_values' => json_encode($dadosAntigos),
                'new_values' => json_encode($alerta->fresh()->toArray()),
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return redirect()->route('alertas.index')
                            ->with('success', 'Alerta atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar alerta: ' . $e->getMessage());
            return back()->withInput()
                        ->with('error', 'Erro ao atualizar alerta. Tente novamente.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $alerta = Alerta::findOrFail($id);

            DB::beginTransaction();

            // Log da ação antes de deletar
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'delete',
                'auditable_type' => 'App\\Models\\Alerta',
                'auditable_id' => $alerta->id,
                'old_values' => json_encode($alerta->toArray()),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl()
            ]);

            $alerta->delete();

            DB::commit();

            Log::info('Alerta excluído com sucesso', ['alerta_id' => $id, 'user_id' => Auth::id()]);

            return redirect()->route('alertas.index')
                           ->with('success', 'Alerta excluído com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir alerta: ' . $e->getMessage());
            return back()->with('error', 'Erro ao excluir alerta.');
        }
    }

    /**
     * Resolver alerta
     */
    public function resolve(Request $request, string $id)
    {
        $request->validate([
            'acoes_tomadas' => 'required|string'
        ]);

        try {
            $alerta = Alerta::findOrFail($id);

            if ($alerta->status === 'resolvido') {
                return back()->with('error', 'Este alerta já foi resolvido.');
            }

            DB::beginTransaction();

            $dadosAntigos = $alerta->toArray();

            $alerta->update([
                'status' => 'resolvido',
                'resolvido_por' => Auth::id(),
                'resolvido_em' => now(),
                'acoes_tomadas' => $request->acoes_tomadas
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'resolve',
                'auditable_type' => 'App\\Models\\Alerta',
                'auditable_id' => $alerta->id,
                'old_values' => json_encode($dadosAntigos),
                'new_values' => json_encode($alerta->fresh()->toArray()),
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return redirect()->route('alertas.index')
                            ->with('success', 'Alerta resolvido com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao resolver alerta: ' . $e->getMessage());
            return back()->with('error', 'Erro ao resolver alerta. Tente novamente.');
        }
    }

    /**
     * Descartar alerta
     */
    public function dismiss(string $id)
    {
        try {
            $alerta = Alerta::findOrFail($id);

            if ($alerta->status === 'resolvido') {
                return back()->with('error', 'Este alerta já foi resolvido.');
            }

            DB::beginTransaction();

            $dadosAntigos = $alerta->toArray();

            $alerta->update([
                'status' => 'cancelado',
                'resolvido_por' => Auth::id(),
                'resolvido_em' => now()
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'dismiss',
                'auditable_type' => 'App\\Models\\Alerta',
                'auditable_id' => $alerta->id,
                'old_values' => json_encode($dadosAntigos),
                'new_values' => json_encode($alerta->fresh()->toArray()),
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return redirect()->route('alertas.index')
                            ->with('success', 'Alerta cancelado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cancelar alerta: ' . $e->getMessage());
            return back()->with('error', 'Erro ao cancelar alerta. Tente novamente.');
        }
    }

    /**
     * Dashboard de alertas
     */
    public function dashboard()
    {
        try {
            $query = Alerta::query();

            // Filtro por usuário se for concessionária
            if (Auth::user()->hasRole('concessionaria')) {
                $query->where('user_id', Auth::id());
            }

            $estatisticas = [
                'total' => $query->count(),
                'ativos' => $query->where('status', 'ativo')->count(),
                'resolvidos' => $query->where('status', 'resolvido')->count(),
                'cancelados' => $query->where('status', 'cancelado')->count(),
                'criticos' => $query->where('nivel', 'critica')->where('status', 'ativo')->count()
            ];

            // Alertas por nível
            $alertasPorNivel = $query->selectRaw('nivel, count(*) as total')
                                ->where('status', 'ativo')
                                ->groupBy('nivel')
                                ->pluck('total', 'nivel')
                                ->toArray();

            return response()->json([
                'estatisticas',
                'alertasPorNivel'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dashboard de alertas: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar dashboard.');
        }
    }

    /**
     * API endpoint para alertas
     */
    public function api(Request $request)
    {
        try {
            $query = Alerta::with(['user', 'resolvidoPor']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('titulo', 'like', "%{$search}%")
                      ->orWhere('descricao', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('nivel')) {
                $query->where('nivel', $request->nivel);
            }

            $alertas = $query->orderBy('nivel', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(15);

            return response()->json($alertas);
        } catch (\Exception $e) {
            Log::error('Erro na API de alertas: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * Gerar número único do alerta
     */
    private function generateNumeroAlerta()
    {
        $ano = date('Y');
        $ultimoNumero = Alerta::whereYear('created_at', $ano)
                             ->max('numero_alerta');

        if ($ultimoNumero) {
            $numero = intval(substr($ultimoNumero, -6)) + 1;
        } else {
            $numero = 1;
        }

        return 'ALT' . $ano . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Enviar notificações para usuários sobre novo alerta
     */
    private function enviarNotificacoes(Alerta $alerta)
    {
        try {
            Log::info("Iniciando envio de notificações para alerta: {$alerta->titulo}");
            
            // Buscar usuários que devem receber notificações
            $usuarios = User::where('is_active', true)
                           ->whereHas('roles', function($query) {
                               $query->whereIn('name', ['admin', 'inspector_isps', 'supervisor_portuario']);
                           })
                           ->get();

            Log::info("Encontrados {$usuarios->count()} usuários para notificar");

            if ($usuarios->isEmpty()) {
                Log::warning("Nenhum usuário encontrado para receber notificações");
                return;
            }

            $notificacoesCriadas = 0;
            $emailsEnviados = 0;
            
            foreach ($usuarios as $usuario) {
                try {
                    Log::info("Criando notificação para usuário: {$usuario->name} (ID: {$usuario->id})");
                    
                    // Preparar dados da notificação
                    $notificationData = [
                        'title' => 'Novo Alerta: ' . $alerta->titulo,
                        'message' => "Um novo alerta de nível {$alerta->nivel} foi criado: {$alerta->descricao}",
                        'priority' => $this->mapearPrioridadeAlerta($alerta->nivel),
                        'category' => 'alert',
                        'related_model' => 'App\\Models\\Alerta',
                        'related_id' => $alerta->id,
                        'action_url' => route('alertas.show', $alerta->id),
                        'action_text' => 'Ver Alerta',
                        'metadata' => [
                            'alerta_tipo' => $alerta->tipo,
                            'alerta_nivel' => $alerta->nivel,
                            'areas_afetadas' => $alerta->areas_afetadas
                        ]
                    ];
                    
                    // Criar notificação usando a estrutura padrão do Laravel
                    $notification = \App\Models\Notification::create([
                        'type' => 'App\\Notifications\\AlertaNotification',
                        'notifiable_type' => 'App\\Models\\User',
                        'notifiable_id' => $usuario->id,
                        'data' => json_encode($notificationData)
                    ]);
                    
                    if ($notification) {
                        $notificacoesCriadas++;
                        Log::info("Notificação criada com sucesso - ID: {$notification->id}");
                    } else {
                        Log::error("Falha ao criar notificação para usuário: {$usuario->id}");
                    }
                    
                    // Enviar e-mail de notificação
                    if ($usuario->email) {
                        try {
                            \Illuminate\Support\Facades\Mail::to($usuario->email)
                                ->send(new \App\Mail\NovoAlertaNotification($alerta));
                            
                            $emailsEnviados++;
                            Log::info("E-mail enviado com sucesso para: {$usuario->email}");
                        } catch (\Exception $emailException) {
                            Log::error("Erro ao enviar e-mail para {$usuario->email}: " . $emailException->getMessage());
                        }
                    } else {
                        Log::warning("Usuário {$usuario->name} não possui e-mail cadastrado");
                    }
                    
                } catch (\Exception $e) {
                    Log::error("Erro ao criar notificação para usuário {$usuario->id}: " . $e->getMessage());
                    Log::error("Stack trace: " . $e->getTraceAsString());
                }
            }

            Log::info("Processo concluído: {$notificacoesCriadas} notificações criadas e {$emailsEnviados} e-mails enviados de {$usuarios->count()} usuários");

        } catch (\Exception $e) {
            Log::error('Erro geral ao enviar notificações do alerta: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Mapear nível do alerta para prioridade da notificação
     */
    private function mapearPrioridadeAlerta($nivel)
    {
        return match($nivel) {
            'emergencia' => 'alta',
            'critico' => 'alta',
            'aviso' => 'media',
            'info' => 'baixa',
            default => 'normal'
        };
    }

    /**
     * Gerar relatório de alertas
     */
    public function relatorioFiltrado(Request $request)
    {
        try {
            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'export',
                'auditable_type' => 'App\\Models\\Alerta',
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            $query = Alerta::with(['user', 'resolvidoPor']);

            // Aplicar os mesmos filtros da listagem
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('titulo', 'like', "%{$search}%")
                      ->orWhere('descricao', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('nivel')) {
                $query->where('nivel', $request->nivel);
            }

            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            $alertas = $query->orderBy('nivel', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->get();

            $formato = $request->get('formato', 'pdf');

            if ($formato === 'excel') {
                return $this->generateExcelReport($alertas, $request);
            }

            return $this->generatePdfReport($alertas, $request);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório de alertas: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar relatório. Tente novamente.');
        }
    }

    /**
     * Gerar relatório PDF
     */
    private function generatePdfReport($alertas, $request)
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
                'status' => $request->get('status'),
                'nivel' => $request->get('nivel'),
                'tipo' => $request->get('tipo')
            ];

            $pdf = \PDF::loadView('alertas.relatorio-pdf', compact('alertas', 'filtros', 'configuracoes'));
            
            return $pdf->download('relatorio-alertas-' . now()->format('Y-m-d-H-i-s') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF de alertas: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gerar relatório Excel
     */
    private function generateExcelReport($alertas, $request)
    {
        try {
            $filtrosAplicados = [];
            if ($request->get('search')) {
                $filtrosAplicados['Busca'] = $request->get('search');
            }
            if ($request->get('status')) {
                $filtrosAplicados['Status'] = $request->get('status');
            }
            if ($request->get('nivel')) {
                $filtrosAplicados['Nível'] = $request->get('nivel');
            }
            if ($request->get('tipo')) {
                $filtrosAplicados['Tipo'] = $request->get('tipo');
            }

            $filename = 'relatorio_alertas_' . now()->format('Y-m-d_H-i-s') . '.xls';
            
            // Gerar conteúdo CSV compatível com Excel
            $csvContent = $this->generateExcelCsv($alertas, $filtrosAplicados);
            
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
            Log::error('Erro ao gerar Excel de alertas: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate CSV content compatible with Excel
     */
    private function generateExcelCsv($alertas, $filtrosAplicados)
    {
        // BOM para UTF-8 (necessário para caracteres especiais no Excel)
        $csv = "\xEF\xBB\xBF";
        
        // Cabeçalho do relatório
        $csv .= "PORTO DE SOYO\n";
        $csv .= "Sistema Integrado de Gestão Portuária\n";
        $csv .= "Relatório de Alertas\n\n";
        
        // Informações do relatório
        $csv .= "Data de Geração:;" . now()->format('d/m/Y H:i:s') . "\n";
        $csv .= "Total de Alertas:;" . $alertas->count() . "\n";
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
        $csv .= "ID;Título;Descrição;Tipo;Nível;Status;Criado por;Data Criação;Resolvido por;Data Resolução\n";
        
        // Dados dos alertas
        foreach ($alertas as $alerta) {
            $csv .= sprintf(
                '%d;"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s"' . "\n",
                $alerta->id,
                str_replace('"', '""', $alerta->titulo ?? ''),
                str_replace('"', '""', $alerta->descricao ?? ''),
                str_replace('"', '""', $alerta->tipo ?? ''),
                str_replace('"', '""', $alerta->nivel ?? ''),
                str_replace('"', '""', $alerta->status ?? ''),
                str_replace('"', '""', $alerta->user->name ?? 'N/A'),
                $alerta->created_at ? $alerta->created_at->format('d/m/Y H:i:s') : '',
                str_replace('"', '""', $alerta->resolvidoPor->name ?? 'N/A'),
                $alerta->resolvido_em ? $alerta->resolvido_em->format('d/m/Y H:i:s') : 'N/A'
            );
        }
        
        $csv .= "\n";
        $csv .= "Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária\n";
        
        return $csv;
    }
}