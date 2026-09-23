<?php

namespace App\Http\Controllers;

use App\Models\EntradaSaidaEmbarcacao;
use App\Models\Embarcacao;
use App\Models\Terminal;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\WeatherService;
use App\Facades\Config;
use App\Services\BillingService;

class EntradaSaidaEmbarcacaoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('role:admin|inspector_cais|concessionaria');
    }

    /**
     * Display a listing of movimentos de entrada/saída
     */
    public function index(Request $request)
    {
        try {
            $query = EntradaSaidaEmbarcacao::with(['embarcacao', 'terminal', 'usuario', 'autorizadoPor'])
                ->when($request->search, function ($q, $search) {
                    return $q->where('numero_movimento', 'like', "%{$search}%")
                           ->orWhereHas('embarcacao', function ($eq) use ($search) {
                               $eq->where('nome', 'like', "%{$search}%")
                                  ->orWhere('imo', 'like', "%{$search}%");
                           });
                })
                ->when($request->status, function ($q, $status) {
                    return $q->where('status', $status);
                })
                ->when($request->tipo_movimento, function ($q, $tipo) {
                    return $q->where('tipo_movimento', $tipo);
                })
                ->when($request->terminal_id, function ($q, $terminal) {
                    return $q->where('terminal_id', $terminal);
                })
                ->when($request->data_inicio && $request->data_fim, function ($q) use ($request) {
                    return $q->whereBetween('data_programada', [$request->data_inicio, $request->data_fim]);
                })
                ->when($request->data && !$request->data_inicio && !$request->data_fim, function ($q) use ($request) {
                    return $q->whereDate('data_programada', $request->data);
                })
                ->when(Auth::user()->hasRole('concessionaria'), function ($q) {
                    return $q->whereHas('embarcacao', function ($eq) {
                        $eq->where('concessionaria_id', Auth::user()->concessionaria_id);
                    });
                })
                ->orderBy('data_programada', 'desc');

            $movimentos = $query->paginate(15)->withQueryString();

            // Calcular estatísticas separadamente
            $estatisticas = [
                'concluidos_hoje' => EntradaSaidaEmbarcacao::where('status', 'concluido')
                    ->whereDate('updated_at', today())
                    ->count()
            ];

            $embarcacoes = Embarcacao::where('is_active', true)->get();
            $terminais = Terminal::where('is_active', true)->get();
            $statusOptions = EntradaSaidaEmbarcacao::getStatusOptions();
            $tipoMovimentoOptions = EntradaSaidaEmbarcacao::getTipoMovimentoOptions();

            return view('entrada-saida.index', compact(
                'movimentos', 'embarcacoes', 'terminais', 'statusOptions', 'tipoMovimentoOptions', 'estatisticas'
            ));
        } catch (\Exception $e) {
            Log::error('Erro ao listar movimentos de entrada/saída: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar movimentos.');
        }
    }

    /**
     * Show the form for creating a new movimento
     */
    public function create()
    {
        try {
            $embarcacoes = Embarcacao::where('is_active', true)->get();
            $terminais = Terminal::with(['bercos', 'guindastes'])->where('is_active', true)->get();
            // Linha 90 - método create()
            $operadores = User::role(['admin', 'inspector_cais'])->get();

            return view('entrada-saida.create', compact('embarcacoes', 'terminais', 'operadores'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de criação de movimento: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Store a newly created movimento
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'embarcacao_id' => 'required|exists:embarcacoes,id',
                'terminal_id' => 'required|exists:terminais,id',
                'tipo_movimento' => 'required|in:entrada,saida',
                'berco' => 'string',
                'guindaste_id' => 'nullable|uuid|exists:guindastes,id',
                'agente_maritimo' => 'nullable|string|max:255',
              //  'capitania_origem' => 'nullable|string|max:255',
               // 'capitania_destino' => 'nullable|string|max:255',
                'porto_origem' => 'nullable|string|max:255',
                'porto_destino' => 'nullable|string|max:255',
                'eta' => 'nullable|date',
                'etd' => 'nullable|date',
                'ata' => 'nullable|date',
                'atd' => 'nullable|date',
                'estado_embarcacao' => 'nullable|string',
                'motivo' => 'nullable|string|max:500',
                'documentos' => 'nullable|array',
                'documentos.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'status' => 'required|string',
                'observacoes' => 'nullable|string|max:1000'
            ], [
                'embarcacao_id.required' => 'A embarcação é obrigatória.',
                'terminal_id.required' => 'O terminal é obrigatório.',
                'tipo_movimento.required' => 'O tipo de movimento é obrigatório.',
                'data_programada.required' => 'A data programada é obrigatória.',
                'berco.required' => 'O berço é obrigatório.',
                'documentos.*.mimes' => 'Os documentos devem ser arquivos PDF, DOC, DOCX, JPG, JPEG ou PNG.',
                'documentos.*.max' => 'Cada documento não pode ser maior que 10MB.'
            ]);

            DB::beginTransaction();

            // Gerar número do movimento
            $numeroMovimento = 'ES-' . date('Y') . '-' . str_pad(
                EntradaSaidaEmbarcacao::whereYear('created_at', date('Y'))->count() + 1, 
                6, '0', STR_PAD_LEFT
            );

            // Processar upload de documentos
            $documentosUpload = [];
            if ($request->hasFile('documentos')) {
                foreach ($request->file('documentos') as $arquivo) {
                    $nomeArquivo = time() . '_' . uniqid() . '_' . $arquivo->getClientOriginalName();
                    $caminho = $arquivo->storeAs('entrada-saida-embarcacao', $nomeArquivo, 'public');
                    $documentosUpload[] = [
                        'nome_original' => $arquivo->getClientOriginalName(),
                        'nome_arquivo' => $nomeArquivo,
                        'caminho' => $caminho,
                        'tamanho' => $arquivo->getSize(),
                        'tipo' => $arquivo->getClientMimeType(),
                        'uploaded_at' => now()
                    ];
                }
            }

            $movimento = EntradaSaidaEmbarcacao::create([
                'numero_movimento' => $numeroMovimento,
                'embarcacao_id' => $validated['embarcacao_id'],
                'terminal_id' => $validated['terminal_id'],
                'user_id' => Auth::id(),
                'tipo_movimento' => $validated['tipo_movimento'],
                'berco' => $validated['berco'],
                'estado_embarcacao' => $validated['estado_embarcacao'],
                'guindaste_id' => $validated['guindaste_id'] ?? null,
                'agente_maritimo' => $validated['agente_maritimo'],
               // 'capitania_origem' => $validated['capitania_origem'],
              //  'capitania_destino' => $validated['capitania_destino'],
                'porto_origem' => $validated['porto_origem'] ?? null,
                'porto_destino' => $validated['porto_destino'] ?? null,
                'eta' => $validated['eta'] ?? null,
                'etd' => $validated['etd'] ?? null,
                'ata' => $validated['ata'] ?? null,
                'atd' => $validated['atd'] ?? null,
                'motivo' => $validated['motivo'],
                'documentos_apresentados' => $documentosUpload,
                'observacoes' => $validated['observacoes'],
                'status' => $validated['status']
            ]);

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'created',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacao',
                'auditable_id' => $movimento->id,
                'description' => 'Movimento de entrada/saída criado',
                'new_values' => $movimento->toArray(),
                'url' => $request->url(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            if (env('WEATHERAPI_KEY') && $movimento->eta) {
                try {
                    $local = Config::get('local_empresa', null) ?: ($movimento->terminal->localizacao ?? 'Soyo, Zaire, Angola');
                    $prev = WeatherService::forecastEtaWithMarine($local, Carbon::parse($movimento->eta));
                    if ($prev) {
                        $movimento->update([
                            'eta_temperatura_c' => $prev['avgtemp_c'] ?? null,
                            'eta_vento_max_kph' => $prev['maxwind_kph'] ?? null,
                            'eta_rajada_kph' => $prev['gust_kph'] ?? null,
                            'eta_chuva_chance_pct' => $prev['daily_chance_of_rain'] ?? null,
                            'eta_swell_altura_m' => $prev['swell_height_m'] ?? null,
                            'eta_swell_periodo_s' => $prev['swell_period_s'] ?? null,
                            'eta_swell_direcao' => $prev['swell_direction'] ?? null,
                            'eta_onda_altura_m' => $prev['wave_height_m'] ?? null,
                            'eta_onda_direcao' => $prev['wave_direction'] ?? null,
                        ]);
                    }
                } catch (\Throwable $ex) {
                    Log::warning('Falha previsão ETA', ['erro' => $ex->getMessage(), 'movimento_id' => $movimento->id]);
                }
            }

            DB::commit();

            Log::info('Movimento de entrada/saída criado com sucesso', [
                'movimento_id' => $movimento->id, 
                'user_id' => Auth::id(),
                'documentos_count' => count($documentosUpload)
            ]);

            return redirect()->route('entrada-saida-embarcacao.show', $movimento)
                           ->with('success', 'Movimento criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar movimento de entrada/saída: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao criar movimento.');
        }
    }

    /**
     * Display the specified movimento
     */
    public function show(EntradaSaidaEmbarcacao $entradaSaida)
    {
        try {
            // Verificar permissão para concessionárias
            if (Auth::user()->hasRole('concessionaria')) {
                if (!$entradaSaida->embarcacao || 
                    $entradaSaida->embarcacao->concessionaria_id !== Auth::user()->concessionaria_id) {
                    abort(403, 'Acesso negado.');
                }
            }

            $entradaSaida->load(['embarcacao', 'terminal', 'usuario', 'autorizadoPor', 'facturas.items']);

            return view('entrada-saida.show', ['movimento' => $entradaSaida]);
        } catch (\Exception $e) {
            Log::error('Erro ao exibir movimento de entrada/saída: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar movimento.');
        }
    }

    /**
     * Show the form for editing the specified movimento
     */
    public function edit(EntradaSaidaEmbarcacao $entradaSaida)
    {
        try {
            // Verificar se pode editar
            if (!$entradaSaida->podeEditar()) {
                return back()->with('error', 'Este movimento não pode ser editado.');
            }

            // Verificar permissão para concessionárias
            if (Auth::user()->hasRole('concessionaria')) {
                if (!$entradaSaida->embarcacao || 
                    $entradaSaida->embarcacao->concessionaria_id !== Auth::user()->concessionaria_id) {
                    abort(403, 'Acesso negado.');
                }
            }

            $embarcacoes = Embarcacao::where('is_active', true)->get();
            $terminais = Terminal::where('is_active', true)->get();
            $operadores = User::role(['admin', 'supervisor_portuario', 'inspector_cais', 'tecnico_comercial'])->get();

            return view('entrada-saida.edit', compact('entradaSaida', 'embarcacoes', 'terminais', 'operadores'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição de movimento: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Update the specified movimento
     */
    public function update(Request $request, EntradaSaidaEmbarcacao $entradaSaida)
    {
        try {
            // Verificar se pode editar
            if (!$entradaSaida->podeEditar()) {
                return back()->with('error', 'Este movimento não pode ser editado.');
            }

            $validated = $request->validate([
                'embarcacao_id' => 'required|exists:embarcacoes,id',
                'terminal_id' => 'required|exists:terminais,id',
                'tipo_movimento' => 'required|in:entrada,saida',
                'berco' => 'string',
                'guindaste_id' => 'nullable|uuid|exists:guindastes,id',
                'data_programada' => 'required|date',
                'data_efetiva' => 'nullable|date',
                'agente_maritimo' => 'nullable|string|max:255',
              //  'capitania_origem' => 'nullable|string|max:255',
               // 'capitania_destino' => 'nullable|string|max:255',
                'porto_origem' => 'nullable|string|max:255',
                'porto_destino' => 'nullable|string|max:255',
                'estado_embarcacao' => 'nullable|string|in:esperado,atracado,operando,partido',
                'eta' => 'nullable|date',
                'etd' => 'nullable|date',
                'ata' => 'nullable|date',
                'atd' => 'nullable|date',
                'motivo' => 'nullable|string|max:500',
                'novos_documentos' => 'nullable|array',
                'novos_documentos.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'documentos_removidos' => 'nullable|string',
                'status' => 'string',
                'observacoes' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            $oldData = $entradaSaida->toArray();
            
            // Processar documentos existentes
            $documentosExistentes = $entradaSaida->documentos_apresentados ?? [];
            
            // Remover documentos marcados para remoção
            if ($request->filled('documentos_removidos')) {
                $indicesRemover = json_decode($request->documentos_removidos, true);
                foreach ($indicesRemover as $indice) {
                    if (isset($documentosExistentes[$indice])) {
                        // Remover arquivo físico
                        Storage::disk('public')->delete($documentosExistentes[$indice]['caminho']);
                        unset($documentosExistentes[$indice]);
                    }
                }
                $documentosExistentes = array_values($documentosExistentes); // Reindexar array
            }
            
            // Processar novos documentos
            if ($request->hasFile('novos_documentos')) {
                foreach ($request->file('novos_documentos') as $arquivo) {
                    $nomeArquivo = time() . '_' . uniqid() . '_' . $arquivo->getClientOriginalName();
                    $caminho = $arquivo->storeAs('entrada-saida-embarcacao', $nomeArquivo, 'public');
                    $documentosExistentes[] = [
                        'nome_original' => $arquivo->getClientOriginalName(),
                        'nome_arquivo' => $nomeArquivo,
                        'caminho' => $caminho,
                        'tamanho' => $arquivo->getSize(),
                        'tipo' => $arquivo->getClientMimeType(),
                        'uploaded_at' => now()
                    ];
                }
            }

            $entradaSaida->update([
                'embarcacao_id' => $validated['embarcacao_id'],
                'terminal_id' => $validated['terminal_id'],
                'tipo_movimento' => $validated['tipo_movimento'],
                'berco' => $validated['berco'],
                'estado_embarcacao' => $validated['estado_embarcacao'],
                'guindaste_id' => $validated['guindaste_id'] ?? $entradaSaida->guindaste_id,
                'data_programada' => $validated['data_programada'],
                'data_efetiva' => $validated['data_efetiva'] ?? $entradaSaida->data_efetiva,
                'agente_maritimo' => $validated['agente_maritimo'],
                //'capitania_origem' => $validated['capitania_origem'],
               // 'capitania_destino' => $validated['capitania_destino'],
                'porto_origem' => $validated['porto_origem'] ?? $entradaSaida->porto_origem,
                'porto_destino' => $validated['porto_destino'] ?? $entradaSaida->porto_destino,
                'eta' => $validated['eta'] ?? $entradaSaida->eta,
                'etd' => $validated['etd'] ?? $entradaSaida->etd,
                'ata' => $validated['ata'] ?? $entradaSaida->ata,
                'atd' => $validated['atd'] ?? $entradaSaida->atd,
                'motivo' => $validated['motivo'],
                'documentos_apresentados' => $documentosExistentes,
                'status' => $validated['status'],
                'observacoes' => $validated['observacoes']
            ]);

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'updated',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacao',
                'auditable_id' => $entradaSaida->id,
                'description' => 'Movimento de entrada/saída atualizado',
                'old_values' => $oldData,
                'new_values' => $entradaSaida->getChanges(),
                'url' => $request->url(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route('entrada-saida-embarcacao.show', $entradaSaida)
                           ->with('success', 'Movimento atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar movimento: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao atualizar movimento.');
        }
    }

    /**
     * Remove the specified movimento
     */
    public function destroy(EntradaSaidaEmbarcacao $entradaSaida)
    {
        try {
            // Verificar se pode excluir
            if (!$entradaSaida->podeExcluir()) {
                return back()->with('error', 'Este movimento não pode ser excluído.');
            }

            DB::beginTransaction();

            $movimentoData = $entradaSaida->toArray();
            $entradaSaida->delete();

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'deleted',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacao',
                'auditable_id' => $entradaSaida->id,
                'description' => 'Movimento de entrada/saída excluído',
                'old_values' => $movimentoData,
                'url' => request()->url(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            Log::info('Movimento de entrada/saída excluído com sucesso', [
                'movimento_id' => $entradaSaida->id, 
                'user_id' => Auth::id()
            ]);

            return redirect()->route('entrada-saida-embarcacao.index')
                           ->with('success', 'Movimento excluído com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir movimento de entrada/saída: ' . $e->getMessage());
            return back()->with('error', 'Erro ao excluir movimento.');
        }
    }

    /**
     * Autorizar movimento
     */
    public function autorizar(Request $request, EntradaSaidaEmbarcacao $entradaSaida)
    {
        try {
            // Verificar permissão
            if (!Auth::user()->hasAnyRole(['admin', 'operador'])) {
                abort(403, 'Acesso negado.');
            }

            if (!$entradaSaida->podeAutorizar()) {
                return back()->with('error', 'Apenas movimentos programados podem ser autorizados.');
            }

            $validated = $request->validate([
                'observacoes_autorizacao' => 'nullable|string|max:500'
            ]);

            DB::beginTransaction();

            $entradaSaida->autorizar(Auth::user(), $validated['observacoes_autorizacao'] ?? null);



            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'authorize',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacao',
                'auditable_id' => $entradaSaida->id,
                'description' => 'Movimento autorizado',
                'new_values' => [
                    'status' => 'autorizado',
                    'autorizado_por' => Auth::id(),
                    'autorizado_em' => now(),
                    'observacoes_autorizacao' => $validated['observacoes_autorizacao'] ?? null
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            Log::info('Movimento de entrada/saída autorizado com sucesso', [
                'movimento_id' => $entradaSaida->id, 
                'user_id' => Auth::id()
            ]);

            return back()->with('success', 'Movimento autorizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao autorizar movimento de entrada/saída: ' . $e->getMessage());
            return back()->with('error', 'Erro ao autorizar movimento.');
        }
    }

    /**
     * Iniciar movimento
     */
    public function iniciar(EntradaSaidaEmbarcacao $entradaSaida)
    {
        try {
            // Verificar permissão
            if (!Auth::user()->hasAnyRole(['admin', 'operador'])) {
                abort(403, 'Acesso negado.');
            }

            if (!$entradaSaida->podeIniciar()) {
                return back()->with('error', 'Apenas movimentos autorizados podem ser iniciados.');
            }

            DB::beginTransaction();

            $entradaSaida->iniciar();



            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'start',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacao',
                'auditable_id' => $entradaSaida->id,
                'description' => 'Movimento iniciado',
                'new_values' => [
                    'status' => 'em_andamento',
                    'data_efetiva' => now()
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            Log::info('Movimento de entrada/saída iniciado com sucesso', [
                'movimento_id' => $entradaSaida->id, 
                'user_id' => Auth::id()
            ]);

            return back()->with('success', 'Movimento iniciado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao iniciar movimento de entrada/saída: ' . $e->getMessage());
            return back()->with('error', 'Erro ao iniciar movimento.');
        }
    }

    /**
     * Atracar embarcação
     */
    public function atracar(EntradaSaidaEmbarcacao $entradaSaida)
    {
        try {
            // Verificar permissão
            if (!Auth::user()->hasAnyRole(['admin', 'operador'])) {
                abort(403, 'Acesso negado.');
            }

            if (!$entradaSaida->podeAtracar()) {
                return back()->with('error', 'Este movimento não pode ser atracado.');
            }

            DB::beginTransaction();

            $entradaSaida->update([
                'status' => 'em_andamento', // Mantém o fluxo padrão
                'estado_embarcacao' => 'atracado',
                'data_efetiva' => now() // Atualiza data efetiva ao atracar
            ]);

            // Atualizar status da embarcação para atracado
            if ($entradaSaida->embarcacao) {
                $entradaSaida->embarcacao->update(['status' => 'atracado']);
            }

            // Billing logic: verifica o contrato de atracação e gera uma factura
            BillingService::generateForEntradaSaida($entradaSaida, 'Atracação');

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'dock',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacao',
                'auditable_id' => $entradaSaida->id,
                'description' => 'Embarcação atracada',
                'new_values' => ['status' => 'em_andamento', 'estado_embarcacao' => 'atracado'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return back()->with('success', 'Embarcação atracada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atracar embarcação: ' . $e->getMessage());
            return back()->with('error', 'Erro ao atracar embarcação.');
        }
    }

    /**
     * Desatracar embarcação
     */
    public function desatracar(Request $request, EntradaSaidaEmbarcacao $entradaSaida)
    {
        try {
            // Verificar permissão
            if (!Auth::user()->hasAnyRole(['admin', 'operador'])) {
                abort(403, 'Acesso negado.');
            }

            if (!$entradaSaida->podeDesatracar()) {
                return back()->with('error', 'Este movimento não pode ser desatracado.');
            }

            $request->validate([
                'atd' => 'required|date'
            ]);

            DB::beginTransaction();

            // Desatracar significa que o navio partiu, então o movimento é concluído
            $entradaSaida->update([
                'status' => 'concluido',
                'estado_embarcacao' => 'partido',
                'atd' => $request->atd
            ]);

            // Atualizar status da embarcação para partido
            if ($entradaSaida->embarcacao) {
                $entradaSaida->embarcacao->update(['status' => 'partido']);
            }

            // Billing logic: calcula a estadia pelo contrato de estadia por dias e cria uma factura para isso
            BillingService::generateForEntradaSaida($entradaSaida, 'Estadia');
            BillingService::generateForEntradaSaida($entradaSaida, 'Saida');

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'undock',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacao',
                'auditable_id' => $entradaSaida->id,
                'description' => 'Embarcação desatracada (concluído)',
                'new_values' => ['status' => 'concluido', 'estado_embarcacao' => 'partido'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return back()->with('success', 'Embarcação desatracada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao desatracar embarcação: ' . $e->getMessage());
            return back()->with('error', 'Erro ao desatracar embarcação.');
        }
    }

    /**
     * Concluir movimento
     */
    public function concluir(Request $request, EntradaSaidaEmbarcacao $entradaSaida)
    {
        try {
            // Verificar permissão
            if (!Auth::user()->hasAnyRole(['admin', 'operador'])) {
                abort(403, 'Acesso negado.');
            }

            if (!$entradaSaida->podeConcluir()) {
                return back()->with('error', 'Apenas movimentos em andamento podem ser concluídos.');
            }

            $validated = $request->validate([
                'observacoes_conclusao' => 'nullable|string|max:500'
            ]);

            DB::beginTransaction();

            $entradaSaida->concluir($validated['observacoes_conclusao'] ?? null);



            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'complete',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacao',
                'auditable_id' => $entradaSaida->id,
                'description' => 'Movimento concluído',
                'new_values' => [
                    'status' => 'concluido',
                    'observacoes_conclusao' => $validated['observacoes_conclusao'] ?? null
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            Log::info('Movimento de entrada/saída concluído com sucesso', [
                'movimento_id' => $entradaSaida->id, 
                'user_id' => Auth::id()
            ]);

            return back()->with('success', 'Movimento concluído com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao concluir movimento de entrada/saída: ' . $e->getMessage());
            return back()->with('error', 'Erro ao concluir movimento.');
        }
    }

    /**
     * Cancelar movimento
     */
    public function cancelar(Request $request, EntradaSaidaEmbarcacao $entradaSaida)
    {
        try {
            // Verificar permissão
            if (!Auth::user()->hasAnyRole(['admin', 'operador'])) {
                abort(403, 'Acesso negado.');
            }

            if (!in_array($entradaSaida->status, ['pendente', 'autorizado', 'em_andamento'])) {
                return back()->with('error', 'Este movimento não pode ser cancelado.');
            }

            $validated = $request->validate([
                'motivo_cancelamento' => 'required|string|max:500'
            ], [
                'motivo_cancelamento.required' => 'O motivo do cancelamento é obrigatório.'
            ]);

            DB::beginTransaction();

            $entradaSaida->cancelar($validated['motivo_cancelamento']);

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'cancel',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacao',
                'auditable_id' => $entradaSaida->id,
                'description' => 'Movimento cancelado',
                'new_values' => [
                    'status' => 'cancelado',
                    'motivo_cancelamento' => $validated['motivo_cancelamento']
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            Log::info('Movimento de entrada/saída cancelado com sucesso', [
                'movimento_id' => $entradaSaida->id, 
                'user_id' => Auth::id()
            ]);

            return back()->with('success', 'Movimento cancelado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cancelar movimento de entrada/saída: ' . $e->getMessage());
            return back()->with('error', 'Erro ao cancelar movimento.');
        }
    }

    /**
     * API endpoint para movimentos
     */
    public function api(Request $request)
    {
        try {
            $query = EntradaSaidaEmbarcacao::with(['embarcacao', 'terminal'])
                ->when(Auth::user()->hasRole('concessionaria'), function ($q) {
                    return $q->whereHas('embarcacao', function ($eq) {
                        $eq->where('concessionaria_id', Auth::user()->concessionaria_id);
                    });
                });

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('tipo_movimento')) {
                $query->where('tipo_movimento', $request->tipo_movimento);
            }

            if ($request->has('terminal_id')) {
                $query->where('terminal_id', $request->terminal_id);
            }

            $movimentos = $query->orderBy('data_programada', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $movimentos
            ]);
        } catch (\Exception $e) {
            Log::error('Erro na API de movimentos de entrada/saída: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar movimentos'
            ], 500);
        }
    }

    /**
     * Dashboard de movimentos
     */
    public function dashboard()
    {
        try {
            $query = EntradaSaidaEmbarcacao::query()
                ->when(Auth::user()->hasRole('concessionaria'), function ($q) {
                    return $q->whereHas('embarcacao', function ($eq) {
                        $eq->where('concessionaria_id', Auth::user()->concessionaria_id);
                    });
                });

            $estatisticas = [
                'total' => $query->count(),
                'pendentes' => $query->where('status', 'pendente')->count(),
                'autorizados' => $query->where('status', 'autorizado')->count(),
                'em_andamento' => $query->where('status', 'em_andamento')->count(),
                'concluidos' => $query->where('status', 'concluido')->count(),
                'cancelados' => $query->where('status', 'cancelado')->count(),
                'entradas_hoje' => $query->where('tipo_movimento', 'entrada')
                                       ->whereDate('data_programada', today())
                                       ->count(),
                'saidas_hoje' => $query->where('tipo_movimento', 'saida')
                                     ->whereDate('data_programada', today())
                                     ->count()
            ];

            $movimentosHoje = $query->with(['embarcacao', 'terminal'])
                                  ->whereDate('data_programada', today())
                                  ->orderBy('data_programada')
                                  ->get();

            $proximosMovimentos = $query->with(['embarcacao', 'terminal'])
                                      ->where('status', 'autorizado')
                                      ->where('data_programada', '>=', today())
                                      ->orderBy('data_programada')
                                      ->limit(10)
                                      ->get();

            return view('entrada-saida.dashboard', compact(
                'estatisticas', 'movimentosHoje', 'proximosMovimentos'
            ));
        } catch (\Exception $e) {
            Log::error('Erro no dashboard de entrada/saída: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar dashboard.');
        }
    }

    /**
     * Relatório de movimentos por período
     */
    public function relatorio(Request $request)
    {
        try {
            $dataInicio = $request->data_inicio ?? today()->subDays(30)->format('Y-m-d');
            $dataFim = $request->data_fim ?? today()->format('Y-m-d');
            $terminalId = $request->terminal_id;
            $tipoMovimento = $request->tipo_movimento;

            $query = EntradaSaidaEmbarcacao::with(['embarcacao', 'terminal', 'usuario'])
                ->whereBetween('data_programada', [$dataInicio, $dataFim])
                ->when($terminalId, function ($q, $terminal) {
                    return $q->where('terminal_id', $terminal);
                })
                ->when($tipoMovimento, function ($q, $tipo) {
                    return $q->where('tipo_movimento', $tipo);
                })
                ->when(Auth::user()->hasRole('concessionaria'), function ($q) {
                    return $q->whereHas('embarcacao', function ($eq) {
                        $eq->where('concessionaria_id', Auth::user()->concessionaria_id);
                    });
                });

            $movimentos = $query->orderBy('data_programada')
                              ->orderBy('tipo_movimento')
                              ->get();

            $estatisticasPeriodo = [
                'total_movimentos' => $movimentos->count(),
                'entradas' => $movimentos->where('tipo_movimento', 'entrada')->count(),
                'saidas' => $movimentos->where('tipo_movimento', 'saida')->count(),
                'concluidos' => $movimentos->where('status', 'concluido')->count(),
                'cancelados' => $movimentos->where('status', 'cancelado')->count(),
                'tempo_medio_duracao' => $movimentos->where('status', 'concluido')
                                                   ->avg(function ($mov) {
                                                       return $mov->calcularDuracao();
                                                   })
            ];

            $terminais = Terminal::where('is_active', true)->get();

            return view('entrada-saida.relatorio', compact(
                'movimentos', 'estatisticasPeriodo', 'terminais', 
                'dataInicio', 'dataFim', 'terminalId', 'tipoMovimento'
            ));
        } catch (\Exception $e) {
            Log::error('Erro no relatório de entrada/saída: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar relatório.');
        }
    }

     /**
     * Revogar movimento concluído para edição (apenas admin)
     */
    public function revogar(Request $request, EntradaSaidaEmbarcacao $entradaSaida)
    {
        try {
            // Verificar se é admin
            if (!Auth::user()->hasRole('admin')) {
                abort(403, 'Apenas administradores podem revogar movimentos concluídos ou cancelado.');
            }

               if (!in_array($entradaSaida->status, ['concluido', 'cancelado'])) {
                return back()->with('error', 'Apenas movimentos concluídos ou cancelados podem ser revogados.');
            }

            $validated = $request->validate([
                'motivo_revogacao' => 'required|string|max:500'
            ], [
                'motivo_revogacao.required' => 'O motivo da revogação é obrigatório.'
            ]);

            DB::beginTransaction();

            // Salvar dados da conclusão antes de revogar
            $dadosAntigos = [
                'status' => $entradaSaida->status,
                'data_conclusao' => $entradaSaida->data_conclusao,
                'observacoes_conclusao' => $entradaSaida->observacoes_conclusao
            ];

            $entradaSaida->revogar($validated['motivo_revogacao']);

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'revoked',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacao',
                'auditable_id' => $entradaSaida->id,
                'description' => 'Movimento revogado para edição',
                'old_values' => $dadosAntigos,
                'new_values' => [
                    'status' => 'em_andamento',
                    'motivo_revogacao' => $validated['motivo_revogacao'],
                    'revogado_por' => Auth::id(),
                    'revogado_em' => now()
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            Log::info('Movimento de entrada/saída revogado com sucesso', [
                'movimento_id' => $entradaSaida->id, 
                'user_id' => Auth::id(),
                'motivo' => $validated['motivo_revogacao']
            ]);

            return back()->with('success', 'Movimento revogado com sucesso! Agora pode ser editado novamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao revogar movimento de entrada/saída: ' . $e->getMessage());
            return back()->with('error', 'Erro ao revogar movimento.');
        }
    }

    /**
     * Exporta um movimento de entrada/saída específico para PDF
     */
    public function exportPdf(EntradaSaidaEmbarcacao $entradaSaida)
    {
        try {
            // Carregando os relacionamentos corretos
            $entradaSaida->load(['embarcacao', 'terminal', 'usuario', 'autorizadoPor']);

            // Obter configurações do sistema
            $configuracoes = [
                'logo_relatorios' => \App\Helpers\ConfigHelper::get('logo_relatorios', ''),
                'cabecalho' => \App\Helpers\ConfigHelper::get('cabecalho_documentos'),
                'rodape' => \App\Helpers\ConfigHelper::get('rodape_relatorios', 'Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária')
            ];

            // Se cabecalho_documentos for string JSON, decodificar
            if (is_string($configuracoes['cabecalho'])) {
                $configuracoes['cabecalho'] = json_decode($configuracoes['cabecalho'], true) ?: $configuracoes['cabecalho'];
            }

            // Se cabecalho_documentos for string JSON, decodificar
            if (is_string($configuracoes['cabecalho'])) {
                $configuracoes['cabecalho'] = json_decode($configuracoes['cabecalho'], true) ?: $configuracoes['cabecalho'];
            }
            
            $pdf = Pdf::loadView('entrada-saida.pdf', compact('entradaSaida', 'configuracoes'));
            
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial'
            ]);
            
            $filename = 'movimento_' . $entradaSaida->numero_movimento . '_' . date('Y-m-d_H-i-s') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF do movimento: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar PDF do movimento.');
        }
    }

    /**
     * Generate report with filtered movimentos (PDF or Excel)
     */
    public function relatorioFiltrado(Request $request)
    {
        try {
            $formato = $request->get('formato', 'pdf'); // Default para PDF
            
            // Aplicar os mesmos filtros do método index
            $query = EntradaSaidaEmbarcacao::with(['embarcacao', 'terminal', 'usuario', 'autorizadoPor'])
                ->when($request->search, function ($q, $search) {
                    return $q->where('numero_movimento', 'like', "%{$search}%")
                           ->orWhereHas('embarcacao', function ($eq) use ($search) {
                               $eq->where('nome', 'like', "%{$search}%")
                                  ->orWhere('imo', 'like', "%{$search}%");
                           });
                })
                ->when($request->status, function ($q, $status) {
                    return $q->where('status', $status);
                })
                ->when($request->tipo_movimento, function ($q, $tipo) {
                    return $q->where('tipo_movimento', $tipo);
                })
                ->when($request->terminal_id, function ($q, $terminal) {
                    return $q->where('terminal_id', $terminal);
                })
                ->when($request->data_inicio && $request->data_fim, function ($q) use ($request) {
                    return $q->whereBetween('data_programada', [$request->data_inicio, $request->data_fim]);
                })
                ->when($request->data && !$request->data_inicio && !$request->data_fim, function ($q) use ($request) {
                    return $q->whereDate('data_programada', $request->data);
                })
                ->when(Auth::user()->hasRole('concessionaria'), function ($q) {
                    return $q->whereHas('embarcacao', function ($eq) {
                        $eq->where('concessionaria_id', Auth::user()->concessionaria_id);
                    });
                });

            $movimentos = $query->orderBy('data_programada', 'desc')->get();

            // Preparar dados dos filtros aplicados
            $filtrosAplicados = [];
            if ($request->search) $filtrosAplicados['Busca'] = $request->search;
            if ($request->status) $filtrosAplicados['Status'] = $request->status;
            if ($request->tipo_movimento) $filtrosAplicados['Tipo'] = $request->tipo_movimento;
            if ($request->terminal_id) {
                $terminal = \App\Models\Terminal::find($request->terminal_id);
                $filtrosAplicados['Terminal'] = $terminal ? $terminal->nome : 'N/A';
            }
            if ($request->data_inicio && $request->data_fim) {
                $filtrosAplicados['Período'] = \Carbon\Carbon::parse($request->data_inicio)->format('d/m/Y') . ' a ' . \Carbon\Carbon::parse($request->data_fim)->format('d/m/Y');
            } elseif ($request->data) {
                $filtrosAplicados['Data'] = \Carbon\Carbon::parse($request->data)->format('d/m/Y');
            }

            // Log da ação
            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'export_report',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacao',
                'auditable_id' => null,
                'description' => "Gerou relatório de movimentos em formato {$formato} com " . $movimentos->count() . " registros",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Gerar relatório baseado no formato
            if ($formato === 'excel') {
                return $this->generateExcelReportMovimentos($movimentos, $filtrosAplicados);
            } else {
                return $this->generatePdfReportMovimentos($movimentos, $filtrosAplicados);
            }

        } catch (\Exception $e) {
            Log::error('EntradaSaidaEmbarcacaoController@relatorioFiltrado: Erro ao gerar relatório', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'filters' => $request->all(),
                'formato' => $formato ?? 'pdf'
            ]);
            
            return back()->with('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF report for movimentos
     */
    private function generatePdfReportMovimentos($movimentos, $filtrosAplicados)
    {
        // Obter configurações do sistema
        $configuracoes = [
            'logo_relatorios' => \App\Helpers\ConfigHelper::get('logo_relatorios', ''),
            'cabecalho' => \App\Helpers\ConfigHelper::get('cabecalho_documentos'),
            'rodape' => \App\Helpers\ConfigHelper::get('rodape_relatorios', 'Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária')
        ];

        // Se cabecalho_documentos for string JSON, decodificar
        if (is_string($configuracoes['cabecalho'])) {
            $configuracoes['cabecalho'] = json_decode($configuracoes['cabecalho'], true) ?: $configuracoes['cabecalho'];
        }

        // Gerar PDF usando DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('entrada-saida.relatorio-pdf', compact('movimentos', 'configuracoes', 'filtrosAplicados'));
        
        // Configurações do PDF
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'Arial',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
            'dpi' => 150
        ]);

        $filename = 'relatorio_movimentos_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Generate Excel report for movimentos
     */
    private function generateExcelReportMovimentos($movimentos, $filtrosAplicados)
    {
        $filename = 'relatorio_movimentos_' . now()->format('Y-m-d_H-i-s') . '.xls';
        
        // Gerar conteúdo CSV compatível com Excel
        $csvContent = $this->generateExcelCsvMovimentos($movimentos, $filtrosAplicados);
        
        // Headers para download compatível com Office 2013
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
            'Pragma' => 'public'
        ];

        return response($csvContent, 200, $headers);
    }

    /**
     * Generate CSV content for Excel export
     */
    private function generateExcelCsvMovimentos($movimentos, $filtrosAplicados)
    {
        $csv = "\xEF\xBB\xBF"; // BOM para UTF-8
        
        // Cabeçalho do relatório
        $csv .= "RELATÓRIO DE MOVIMENTOS DE ENTRADA/SAÍDA\n";
        $csv .= "Gerado em: " . now()->format('d/m/Y H:i:s') . "\n";
        $csv .= "Total de registros: " . $movimentos->count() . "\n\n";
        
        // Filtros aplicados
        if (!empty($filtrosAplicados)) {
            $csv .= "FILTROS APLICADOS:\n";
            foreach ($filtrosAplicados as $filtro => $valor) {
                $csv .= "{$filtro}: {$valor}\n";
            }
            $csv .= "\n";
        }
        
        // Cabeçalhos das colunas
        $csv .= "Número;Tipo;Embarcação;IMO;Terminal;Data Programada;Status;Agente Marítimo;Motivo;Operador;Data Criação\n";
        
        // Dados
        foreach ($movimentos as $movimento) {
            $csv .= sprintf(
                "%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
                $movimento->numero_movimento ?? '',
                ucfirst($movimento->tipo_movimento ?? ''),
                $movimento->embarcacao->nome ?? '',
                $movimento->embarcacao->imo ?? '',
                $movimento->terminal->nome ?? '',
                $movimento->data_programada ? \Carbon\Carbon::parse($movimento->data_programada)->format('d/m/Y H:i') : '',
                ucfirst($movimento->status ?? ''),
                $movimento->agente_maritimo ?? '',
                $movimento->motivo ?? '',
                $movimento->usuario->name ?? '',
                $movimento->created_at ? $movimento->created_at->format('d/m/Y H:i') : ''
            );
        }
        
        return $csv;
    }

    public function weatherPreview(Request $request)
    {
        if (!env('WEATHERAPI_KEY')) { return response()->json(['error' => 'WeatherAPI não configurado'], 400); }
        $id = $request->query('id');
        $etaParam = $request->query('eta');
        if ($id) {
            $mov = EntradaSaidaEmbarcacao::with('terminal')->find($id);
            if (!$mov || !$mov->eta) { return response()->json(['error' => 'ETA não disponível para este movimento'], 404); }
            $local = Config::get('local_empresa', null) ?: ($mov->terminal->localizacao ?? 'Soyo, Zaire, Angola');
            $forecast = WeatherService::forecastForDate($local, $mov->eta);
            return response()->json(['forecast' => $forecast, 'location' => $local, 'eta' => $mov->eta->toDateString()]);
        }
        if (!$etaParam) { return response()->json(['error' => 'ETA é obrigatório'], 400); }
        $local = Config::get('local_empresa', null) ?: 'Soyo, Zaire, Angola';
        $forecast = WeatherService::forecastForDate($local, Carbon::parse($etaParam));
        return response()->json(['forecast' => $forecast, 'location' => $local, 'eta' => Carbon::parse($etaParam)->toDateString()]);
    }
}