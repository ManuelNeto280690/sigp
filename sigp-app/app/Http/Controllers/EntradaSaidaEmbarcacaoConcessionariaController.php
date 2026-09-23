<?php

namespace App\Http\Controllers;

use App\Models\EntradaSaidaEmbarcacaoConcessionaria;
use App\Models\EmbarcacaoConcessionaria;
use App\Models\Concessionaria;
use App\Models\Terminal;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\BillingService;

class EntradaSaidaEmbarcacaoConcessionariaController extends Controller
{
    /*public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('role:admin|inspector_cais|concessionaria');
    }*/

    /**
     * Display a listing of movimentos de entrada/saída
     */
    public function index(Request $request)
    {
        try {
            $query = EntradaSaidaEmbarcacaoConcessionaria::with(['embarcacaoConcessionaria', 'concessionaria', 'terminal', 'usuario', 'autorizadoPor'])
                ->when($request->search, function ($q, $search) {
                    return $q->where('numero_movimento', 'like', "%{$search}%")
                           ->orWhereHas('embarcacaoConcessionaria', function ($eq) use ($search) {
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
                });

            // Filtro por concessionária baseado no tipo de usuário
            if (Auth::user()->hasRole('concessionaria')) {
                // Se o usuário é de concessionária, mostrar apenas movimentos da sua concessionária
                $userConcessionariaId = Auth::user()->concessionaria_id;
                if ($userConcessionariaId) {
                    $query->where('concessionaria_id', $userConcessionariaId);
                } else {
                    // Se não tem concessionária associada, não mostrar nenhum movimento
                    $query->where('id', null);
                }
            }
            // Se for admin ou outros roles, mostrar todos os movimentos (sem filtro adicional)

            $movimentos = $query->orderBy('data_programada', 'desc')->paginate(15)->withQueryString();

            // Calcular estatísticas baseadas no mesmo filtro
            $estatisticasQuery = EntradaSaidaEmbarcacaoConcessionaria::query();
            
            if (Auth::user()->hasRole('concessionaria')) {
                $userConcessionariaId = Auth::user()->concessionaria_id;
                if ($userConcessionariaId) {
                    $estatisticasQuery->where('concessionaria_id', $userConcessionariaId);
                } else {
                    $estatisticasQuery->where('id', null);
                }
            }

            $estatisticas = [
                'concluidos_hoje' => $estatisticasQuery->where('status', 'concluido')
                    ->whereDate('updated_at', today())
                    ->count(),
                'total_movimentos' => $estatisticasQuery->count(),
                'programados' => $estatisticasQuery->where('status', 'programado')->count(),
                'em_andamento' => $estatisticasQuery->where('status', 'em_andamento')->count()
            ];

            // Filtrar embarcações e concessionárias baseado no tipo de usuário
            if (Auth::user()->hasRole('concessionaria')) {
                $userConcessionariaId = Auth::user()->concessionaria_id;
                $embarcacoesConcessionaria = EmbarcacaoConcessionaria::where('is_active', true)
                    ->when($userConcessionariaId, function ($q) use ($userConcessionariaId) {
                        return $q->where('concessionaria_id', $userConcessionariaId);
                    })
                    ->get();
                
                $concessionarias = Concessionaria::where('is_active', true)
                    ->when($userConcessionariaId, function ($q) use ($userConcessionariaId) {
                        return $q->where('id', $userConcessionariaId);
                    })
                    ->get();
            } else {
                // Admin vê todas as embarcações e concessionárias
                $embarcacoesConcessionaria = EmbarcacaoConcessionaria::where('is_active', true)->get();
                $concessionarias = Concessionaria::where('is_active', true)->get();
            }

            $terminais = Terminal::where('is_active', true)->get();
            $statusOptions = EntradaSaidaEmbarcacaoConcessionaria::getStatusOptions();
            $tipoMovimentoOptions = EntradaSaidaEmbarcacaoConcessionaria::getTipoMovimentoOptions();

            return view('concessionarias.entrada-saida.index', compact(
                'movimentos', 'embarcacoesConcessionaria', 'concessionarias', 'terminais', 'statusOptions', 'tipoMovimentoOptions', 'estatisticas'
            ));
        } catch (\Exception $e) {
            Log::error('Erro ao listar movimentos de entrada/saída de concessionária: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar movimentos.');
        }
    }

    /**
     * Show the form for creating a new movimento
     */
    public function create()
    {
        try {
            // Filtrar embarcações pela concessionária do usuário logado
            $embarcacoesQuery = EmbarcacaoConcessionaria::where('is_active', true);
            
            if (Auth::user()->hasRole('concessionaria')) {
                $userConcessionariaId = Auth::user()->concessionaria_id;
                if ($userConcessionariaId) {
                    $embarcacoesQuery->where('concessionaria_id', $userConcessionariaId);
                } else {
                    // Se não tem concessionária associada, não mostrar nenhuma embarcação
                    $embarcacoesQuery->where('id', null);
                }
            }
            
            $embarcacoes = $embarcacoesQuery->get();
            $concessionarias = Concessionaria::where('is_active', true)->get();
            $terminais = Terminal::where('is_active', true)->get();
            $usuarios = User::where('is_active', true)->get();

            return view('concessionarias.entrada-saida.create', compact(
                'embarcacoes', 'concessionarias', 'terminais', 'usuarios'
            ));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de criação: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Store a newly created movimento
     */
    public function store(Request $request)
    {
        try {
            // Determinar se o usuário tem concessionária associada
            $userConcessionariaId = Auth::user()->concessionaria_id;
            
            $validated = $request->validate([
                'embarcacao_id' => 'required|exists:embarcacao_concessionaria,id',
                'concessionaria_id' => $userConcessionariaId ? 'nullable|exists:concessionarias,id' : 'required|exists:concessionarias,id',
                'terminal_id' => 'required|exists:terminais,id',
                'tipo_movimento' => 'required|in:entrada,saida',
                'data_programada' => 'required|date|after_or_equal:today',
                'data_efetiva' => 'nullable|date',
                'berco' => 'nullable|string|max:255',
                'agente_maritimo' => 'nullable|string|max:255',
                'capitania_origem' => 'nullable|string|max:255',
                'capitania_destino' => 'nullable|string|max:255',
                'motivo' => 'nullable|string|max:500',
                'documentos' => 'nullable|array',
                'documentos.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'status' => 'required|in:programado,autorizado,em_andamento,concluido,cancelado',
                'observacoes' => 'nullable|string|max:1000'
            ], [
                'embarcacao_id.required' => 'A embarcação é obrigatória.',
                'concessionaria_id.required' => 'A concessionária é obrigatória.',
                'terminal_id.required' => 'O terminal é obrigatório.',
                'tipo_movimento.required' => 'O tipo de movimento é obrigatório.',
                'data_programada.required' => 'A data programada é obrigatória.',
                'data_programada.after_or_equal' => 'A data programada deve ser hoje ou futura.',
                'data_efetiva.date' => 'A data efetiva deve ser uma data válida.',
                'documentos.*.mimes' => 'Os documentos devem ser arquivos PDF, DOC, DOCX, JPG, JPEG ou PNG.',
                'documentos.*.max' => 'Cada documento não pode ser maior que 10MB.'
            ]);

            DB::beginTransaction();

            // Gerar número do movimento
            $numeroMovimento = 'ESC-' . date('Y') . '-' . str_pad(
                EntradaSaidaEmbarcacaoConcessionaria::whereYear('created_at', date('Y'))->count() + 1, 
                6, '0', STR_PAD_LEFT
            );

            // Processar upload de documentos
            $documentosUpload = [];
            if ($request->hasFile('documentos')) {
                foreach ($request->file('documentos') as $arquivo) {
                    $nomeArquivo = time() . '_' . uniqid() . '_' . $arquivo->getClientOriginalName();
                    $caminho = $arquivo->storeAs('entrada-saida-concessionaria', $nomeArquivo, 'public');
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

            // Usar a concessionária do usuário logado se ele tiver uma, senão usar a do formulário
            $concessionariaId = $userConcessionariaId ?? $validated['concessionaria_id'];

            $movimento = EntradaSaidaEmbarcacaoConcessionaria::create([
                'numero_movimento' => $numeroMovimento,
                'embarcacao_id' => $validated['embarcacao_id'],
                'concessionaria_id' => $concessionariaId,
                'terminal_id' => $validated['terminal_id'],
                'user_id' => Auth::id(),
                'tipo_movimento' => $validated['tipo_movimento'],
                'data_programada' => $validated['data_programada'],
                'agente_maritimo' => $validated['agente_maritimo'],
                'capitania_origem' => $validated['capitania_origem'],
                'capitania_destino' => $validated['capitania_destino'],
                'motivo' => $validated['motivo'],
                'documentos_apresentados' => $documentosUpload,
                'observacoes' => $validated['observacoes'],
                'status' => $validated['status']
            ]);

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'created',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacaoConcessionaria',
                'auditable_id' => $movimento->id,
                'description' => 'Movimento de entrada/saída de concessionária criado',
                'new_values' => $movimento->toArray(),
                'url' => $request->url(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            Log::info('Movimento de entrada/saída de concessionária criado com sucesso', [
                'movimento_id' => $movimento->id, 
                'user_id' => Auth::id(),
                'documentos_count' => count($documentosUpload)
            ]);

            return redirect()->route('concessionarias.entrada-saida-embarcacao-concessionaria.show', $movimento)
                           ->with('success', 'Movimento criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar movimento de concessionária: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao criar movimento.');
        }
    }

    /**
     * Display the specified movimento
     */
    public function show(EntradaSaidaEmbarcacaoConcessionaria $entradaSaidaConcessionaria)
    {
        
        try {
            // Verificar permissão para concessionárias
            if (Auth::user()->hasRole('concessionaria')) {
                if ($entradaSaidaConcessionaria->concessionaria_id !== Auth::user()->concessionaria_id) {
                    abort(403, 'Acesso negado.');
                }
            }

            $entradaSaidaConcessionaria->load(['embarcacaoConcessionaria', 'concessionaria', 'terminal', 'usuario', 'autorizadoPor', 'facturas.items']);

            return view('concessionarias.entrada-saida.show', ['movimento' => $entradaSaidaConcessionaria]);
        } catch (\Exception $e) {
            Log::error('Erro ao exibir movimento de entrada/saída de concessionária: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar movimento.');
        }
    }

    /**
     * Show the form for editing the specified movimento
     */
    public function edit(EntradaSaidaEmbarcacaoConcessionaria $entradaSaidaConcessionaria)
    {
        try {
            // Verificar se pode editar
         /*   if (!$entradaSaidaConcessionaria->podeEditar()) {
                return back()->with('error', 'Este movimento não pode ser editado.');
            }*/

            // Verificar permissão para concessionárias
            if (Auth::user()->hasRole('concessionaria')) {
                if ($entradaSaidaConcessionaria->concessionaria_id !== Auth::user()->concessionaria_id) {
                    abort(403, 'Acesso negado.');
                }
            }

            // Filtrar embarcações pela concessionária do usuário logado
            $embarcacoesQuery = EmbarcacaoConcessionaria::where('is_active', true);
            
            if (Auth::user()->hasRole('concessionaria')) {
                $userConcessionariaId = Auth::user()->concessionaria_id;
                if ($userConcessionariaId) {
                    $embarcacoesQuery->where('concessionaria_id', $userConcessionariaId);
                } else {
                    // Se não tem concessionária associada, não mostrar nenhuma embarcação
                    $embarcacoesQuery->where('id', null);
                }
            }
            
            $entradaSaida = $entradaSaidaConcessionaria;
            $embarcacoes = $embarcacoesQuery->get();
            $concessionarias = Concessionaria::where('is_active', true)->get();
            $terminais = Terminal::where('is_active', true)->get();
            $operadores = User::role(['admin', 'supervisor_portuario', 'inspector_cais', 'tecnico_comercial'])->get();

            return view('concessionarias.entrada-saida.edit', compact('entradaSaida', 'embarcacoes', 'concessionarias', 'terminais', 'operadores'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição de movimento de concessionária: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Update the specified movimento
     */
    public function update(Request $request, EntradaSaidaEmbarcacaoConcessionaria $entradaSaidaConcessionaria)
    {
        try {
            // Verificar se pode editar
         /*   if (!$entradaSaidaConcessionaria->podeEditar()) {
                return back()->with('error', 'Este movimento não pode ser editado.');
            }*/

            // Verificar permissão para concessionárias
            if (Auth::user()->hasRole('concessionaria')) {
                if ($entradaSaidaConcessionaria->concessionaria_id !== Auth::user()->concessionaria_id) {
                    abort(403, 'Acesso negado.');
                }
            }

            // Definir se a concessionária é obrigatória baseado no usuário
            $userConcessionariaId = Auth::user()->concessionaria_id;

            $validated = $request->validate([
                'embarcacao_id' => 'required|exists:embarcacao_concessionaria,id',
                'concessionaria_id' => $userConcessionariaId ? 'nullable|exists:concessionarias,id' : 'required|exists:concessionarias,id',
                'terminal_id' => 'required|exists:terminais,id',
                'tipo_movimento' => 'required|in:entrada,saida',
                'data_programada' => 'required|date|after_or_equal:today',
                'data_efetiva' => 'nullable|date',
                'berco' => 'nullable|string|max:255',
                'agente_maritimo' => 'nullable|string|max:255',
                'capitania_origem' => 'nullable|string|max:255',
                'capitania_destino' => 'nullable|string|max:255',
                'motivo' => 'nullable|string|max:500',
                'documentos' => 'nullable|array',
                'documentos.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'status' => 'required|in:programado,autorizado,em_andamento,concluido,cancelado',
                'observacoes' => 'nullable|string|max:1000'
            ], [
                'embarcacao_id.required' => 'A embarcação é obrigatória.',
                'concessionaria_id.required' => 'A concessionária é obrigatória.',
                'terminal_id.required' => 'O terminal é obrigatório.',
                'tipo_movimento.required' => 'O tipo de movimento é obrigatório.',
                'data_programada.required' => 'A data programada é obrigatória.',
                'data_programada.after_or_equal' => 'A data programada deve ser hoje ou futura.',
                'data_efetiva.date' => 'A data efetiva deve ser uma data válida.',
                'documentos.*.mimes' => 'Os documentos devem ser arquivos PDF, DOC, DOCX, JPG, JPEG ou PNG.',
                'documentos.*.max' => 'Cada documento não pode ser maior que 10MB.'
            ]);

            DB::beginTransaction();

            $oldData = $entradaSaidaConcessionaria->toArray();
            
            // Usar a concessionária do usuário se ele for de concessionária
            if ($userConcessionariaId) {
                $validated['concessionaria_id'] = $userConcessionariaId;
            }
            
            // Processar documentos existentes
            $documentosExistentes = $entradaSaidaConcessionaria->documentos_apresentados ?? [];
            
            // Remover documentos marcados para remoção
            if ($request->filled('documentos_removidos')) {
                $indicesRemover = json_decode($request->documentos_removidos, true);
                if (is_array($indicesRemover)) {
                    foreach ($indicesRemover as $indice) {
                        if (isset($documentosExistentes[$indice])) {
                            // Remover arquivo físico se existir
                            if (isset($documentosExistentes[$indice]['caminho'])) {
                                Storage::disk('public')->delete($documentosExistentes[$indice]['caminho']);
                            }
                            unset($documentosExistentes[$indice]);
                        }
                    }
                    $documentosExistentes = array_values($documentosExistentes); // Reindexar array
                }
            }
            
            // Processar novos documentos
            if ($request->hasFile('novos_documentos')) {
                foreach ($request->file('novos_documentos') as $arquivo) {
                    $nomeArquivo = time() . '_' . uniqid() . '_' . $arquivo->getClientOriginalName();
                    $caminho = $arquivo->storeAs('entrada-saida-concessionaria', $nomeArquivo, 'public');
                    $documentosExistentes[] = [
                        'nome_original' => $arquivo->getClientOriginalName(),
                        'nome_arquivo' => $nomeArquivo,
                        'caminho' => $caminho,
                        'tamanho' => $arquivo->getSize(),
                        'tipo' => $arquivo->getClientMimeType(),
                        'uploaded_at' => now()->toDateTimeString()
                    ];
                }
            }

            $entradaSaidaConcessionaria->update([
                'embarcacao_id' => $validated['embarcacao_id'],
                'concessionaria_id' => $validated['concessionaria_id'],
                'terminal_id' => $validated['terminal_id'],
                'tipo_movimento' => $validated['tipo_movimento'],
                'data_programada' => $validated['data_programada'],
                'data_efetiva' => $validated['data_efetiva'],
                'berco' => $validated['berco'],
                'agente_maritimo' => $validated['agente_maritimo'],
                'capitania_origem' => $validated['capitania_origem'],
                'capitania_destino' => $validated['capitania_destino'],
                'motivo' => $validated['motivo'],
                'documentos_apresentados' => $documentosExistentes,
                'status' => $validated['status'],
                'observacoes' => $validated['observacoes']
            ]);

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'updated',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacaoConcessionaria',
                'auditable_id' => $entradaSaidaConcessionaria->id,
                'description' => 'Movimento de entrada/saída de concessionária atualizado',
                'old_values' => $oldData,
                'new_values' => $entradaSaidaConcessionaria->getChanges(),
                'url' => $request->url(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route('concessionarias.entrada-saida-embarcacao-concessionaria.index')
                           ->with('success', 'Movimento atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar movimento de concessionária: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao atualizar movimento: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified movimento
     */
    public function destroy(EntradaSaidaEmbarcacaoConcessionaria $entradaSaidaConcessionaria)
    {
        try {
            // Verificar se pode excluir
            if (!$entradaSaidaConcessionaria->podeExcluir()) {
                return back()->with('error', 'Este movimento não pode ser excluído.');
            }

            DB::beginTransaction();

            $movimentoData = $entradaSaidaConcessionaria->toArray();
            $entradaSaidaConcessionaria->delete();

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'deleted',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacaoConcessionaria',
                'auditable_id' => $entradaSaidaConcessionaria->id,
                'description' => 'Movimento de entrada/saída de concessionária excluído',
                'old_values' => $movimentoData,
                'url' => request()->url(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            Log::info('Movimento de entrada/saída de concessionária excluído com sucesso', [
                'movimento_id' => $entradaSaidaConcessionaria->id, 
                'user_id' => Auth::id()
            ]);

            return redirect()->route('concessionarias.entrada-saida-embarcacao-concessionaria.index')
                           ->with('success', 'Movimento excluído com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir movimento de concessionária: ' . $e->getMessage());
            return back()->with('error', 'Erro ao excluir movimento.');
        }
    }

    /**
     * Autorizar movimento
     */
    public function autorizar(Request $request, EntradaSaidaEmbarcacaoConcessionaria $entradaSaidaConcessionaria)
    {
        try {
            // Verificar permissão
            if (!Auth::user()->hasAnyRole(['admin', 'operador'])) {
                abort(403, 'Acesso negado.');
            }

            if (!$entradaSaidaConcessionaria->podeAutorizar()) {
                return back()->with('error', 'Apenas movimentos programados podem ser autorizados.');
            }

            $validated = $request->validate([
                'observacoes_autorizacao' => 'nullable|string|max:500'
            ]);

            DB::beginTransaction();

            $entradaSaidaConcessionaria->autorizar(Auth::user(), $validated['observacoes_autorizacao'] ?? null);

            // Log de auditoria
           AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'authorize',
            'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacaoConcessionaria',
            'auditable_id' => $entradaSaidaConcessionaria->id,
            'description' => 'Entrada/Saída autorizada',
            'new_values' => json_encode([
                'status' => 'autorizado',
                'autorizado_por' => Auth::id(),
                'autorizado_em' => now()
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl()
        ]);

            DB::commit();

            Log::info('Movimento de entrada/saída de concessionária autorizado com sucesso', [
                'movimento_id' => $entradaSaidaConcessionaria->id, 
                'user_id' => Auth::id()
            ]);

            return back()->with('success', 'Movimento autorizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao autorizar movimento de entrada/saída de concessionária: ' . $e->getMessage());
            return back()->with('error', 'Erro ao autorizar movimento.');
        }
    }

    /**
     * Iniciar movimento
     */
    public function iniciar(EntradaSaidaEmbarcacaoConcessionaria $entradaSaidaConcessionaria)
    {
        try {
            // Verificar permissão
            if (!Auth::user()->hasAnyRole(['admin', 'operador'])) {
                abort(403, 'Acesso negado.');
            }

            if ($entradaSaidaConcessionaria->status !== 'autorizado') {
                return back()->with('error', 'Apenas movimentos autorizados podem ser iniciados.');
            }

            DB::beginTransaction();

            $entradaSaidaConcessionaria->iniciar();

            if ($entradaSaidaConcessionaria->tipo_movimento === 'entrada') {
                BillingService::generateForEntradaSaida($entradaSaidaConcessionaria, 'entrada');
            }

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'start',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacaoConcessionaria',
                'auditable_id' => $entradaSaidaConcessionaria->id,
                'description' => 'Movimento iniciado',
                'new_values' => json_encode([
                    'status' => 'em_andamento',
                    'data_efetiva' => now()
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl()
            ]);

            DB::commit();

            Log::info('Movimento de entrada/saída de concessionária iniciado com sucesso', [
                'movimento_id' => $entradaSaidaConcessionaria->id, 
                'user_id' => Auth::id()
            ]);

            return back()->with('success', 'Movimento iniciado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao iniciar movimento de entrada/saída de concessionária: ' . $e->getMessage());
            return back()->with('error', 'Erro ao iniciar movimento.');
        }
    }

    /**
     * Concluir movimento
     */
    public function concluir(Request $request, EntradaSaidaEmbarcacaoConcessionaria $entradaSaidaConcessionaria)
    {
        try {
            // Verificar permissão
            if (!Auth::user()->hasAnyRole(['admin', 'operador'])) {
                abort(403, 'Acesso negado.');
            }

            if ($entradaSaidaConcessionaria->status !== 'em_andamento') {
                return back()->with('error', 'Apenas movimentos em andamento podem ser concluídos.');
            }

            $validated = $request->validate([
                'observacoes_conclusao' => 'nullable|string|max:500'
            ]);

            DB::beginTransaction();

            $entradaSaidaConcessionaria->concluir($validated['observacoes_conclusao'] ?? null);

            if ($entradaSaidaConcessionaria->tipo_movimento === 'saida') {
                BillingService::generateForEntradaSaida($entradaSaidaConcessionaria, 'saida');
            }

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'complete',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacaoConcessionaria',
                'auditable_id' => $entradaSaidaConcessionaria->id,
                'description' => 'Movimento concluído',
                'new_values' => json_encode([
                    'status' => 'concluido'
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl()
            ]);

            DB::commit();

            Log::info('Movimento de entrada/saída de concessionária concluído com sucesso', [
                'movimento_id' => $entradaSaidaConcessionaria->id, 
                'user_id' => Auth::id()
            ]);

            return back()->with('success', 'Movimento concluído com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao concluir movimento de entrada/saída de concessionária: ' . $e->getMessage());
            return back()->with('error', 'Erro ao concluir movimento.');
        }
    }

    /**
     * Cancelar movimento
     */
    public function cancelar(Request $request, EntradaSaidaEmbarcacaoConcessionaria $entradaSaidaConcessionaria)
    {
        try {
            // Verificar permissão
            if (!Auth::user()->hasAnyRole(['admin', 'operador'])) {
                abort(403, 'Acesso negado.');
            }

            if (!in_array($entradaSaidaConcessionaria->status, ['pendente', 'autorizado', 'em_andamento'])) {
                return back()->with('error', 'Este movimento não pode ser cancelado.');
            }

            $validated = $request->validate([
                'motivo_cancelamento' => 'required|string|max:500'
            ], [
                'motivo_cancelamento.required' => 'O motivo do cancelamento é obrigatório.'
            ]);

            DB::beginTransaction();

            $entradaSaidaConcessionaria->cancelar($validated['motivo_cancelamento']);

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'cancel',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacaoConcessionaria',
                'auditable_id' => $entradaSaidaConcessionaria->id,
                'description' => 'Movimento cancelado',
                'new_values' => json_encode([
                    'status' => 'cancelado',
                    'motivo_cancelamento' => $validated['motivo_cancelamento']
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl()
            ]);

            DB::commit();

            Log::info('Movimento de entrada/saída de concessionária cancelado com sucesso', [
                'movimento_id' => $entradaSaidaConcessionaria->id, 
                'user_id' => Auth::id()
            ]);

            return back()->with('success', 'Movimento cancelado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cancelar movimento de entrada/saída de concessionária: ' . $e->getMessage());
            return back()->with('error', 'Erro ao cancelar movimento.');
        }
    }

    /**
     * API endpoint para movimentos
     */
    public function api(Request $request)
    {
        try {
            $query = EntradaSaidaEmbarcacaoConcessionaria::with(['embarcacaoConcessionaria', 'concessionaria', 'terminal'])
                ->when(Auth::user()->hasRole('concessionaria'), function ($q) {
                    return $q->where('concessionaria_id', Auth::user()->concessionaria_id);
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
            Log::error('Erro na API de movimentos de entrada/saída de concessionária: ' . $e->getMessage());
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
            $query = EntradaSaidaEmbarcacaoConcessionaria::query()
                ->when(Auth::user()->hasRole('concessionaria'), function ($q) {
                    return $q->where('concessionaria_id', Auth::user()->concessionaria_id);
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

            $movimentosHoje = $query->with(['embarcacaoConcessionaria', 'concessionaria', 'terminal'])
                                  ->whereDate('data_programada', today())
                                  ->orderBy('data_programada')
                                  ->get();

            $proximosMovimentos = $query->with(['embarcacaoConcessionaria', 'concessionaria', 'terminal'])
                                      ->where('status', 'autorizado')
                                      ->where('data_programada', '>=', today())
                                      ->orderBy('data_programada')
                                      ->limit(10)
                                      ->get();

            return view('entrada-saida-concessionaria.dashboard', compact(
                'estatisticas', 'movimentosHoje', 'proximosMovimentos'
            ));
        } catch (\Exception $e) {
            Log::error('Erro no dashboard de entrada/saída de concessionária: ' . $e->getMessage());
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

            $query = EntradaSaidaEmbarcacaoConcessionaria::with(['embarcacaoConcessionaria', 'concessionaria', 'terminal', 'usuario'])
                ->whereBetween('data_programada', [$dataInicio, $dataFim])
                ->when($terminalId, function ($q, $terminal) {
                    return $q->where('terminal_id', $terminal);
                })
                ->when($tipoMovimento, function ($q, $tipo) {
                    return $q->where('tipo_movimento', $tipo);
                })
                ->when(Auth::user()->hasRole('concessionaria'), function ($q) {
                    return $q->where('concessionaria_id', Auth::user()->concessionaria_id);
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

            return view('entrada-saida-concessionaria.relatorio', compact(
                'movimentos', 'estatisticasPeriodo', 'terminais', 
                'dataInicio', 'dataFim', 'terminalId', 'tipoMovimento'
            ));
        } catch (\Exception $e) {
            Log::error('Erro no relatório de entrada/saída de concessionária: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar relatório.');
        }
    }

    /**
     * Gerar relatório filtrado de movimentos de entrada/saída
     */
    public function relatorioFiltrado(Request $request)
    {
        try {
            // Aplicar filtros
            $query = EntradaSaidaEmbarcacaoConcessionaria::with(['embarcacaoConcessionaria', 'concessionaria', 'terminal', 'usuario', 'autorizadoPor']);

            // Filtro por concessionária (para usuários de concessionária)
            if (Auth::user()->hasRole('concessionaria')) {
                $user = Auth::user();
                $userConcessionariaId = $user->concessionaria_id;
                
                if ($userConcessionariaId) {
                    $query->where('concessionaria_id', $userConcessionariaId);
                } else {
                    $query->where('id', null);
                }
            }

            // Aplicar filtros da requisição
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('numero_movimento', 'like', "%{$search}%")
                      ->orWhereHas('embarcacaoConcessionaria', function ($eq) use ($search) {
                          $eq->where('nome', 'like', "%{$search}%")
                             ->orWhere('imo', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('tipo_movimento')) {
                $query->where('tipo_movimento', $request->tipo_movimento);
            }

            if ($request->filled('terminal_id')) {
                $query->where('terminal_id', $request->terminal_id);
            }

            if ($request->filled('concessionaria_id')) {
                $query->where('concessionaria_id', $request->concessionaria_id);
            }

            if ($request->filled('data_inicio')) {
                $query->whereDate('data_movimento', '>=', $request->data_inicio);
            }

            if ($request->filled('data_fim')) {
                $query->whereDate('data_movimento', '<=', $request->data_fim);
            }

            $movimentos = $query->orderBy('created_at', 'desc')->get();

            // Registrar auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'report_generated',
                'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacaoConcessionaria',
                'auditable_id' => null,
                'description' => 'Relatório de movimentos de entrada/saída gerado',
                'old_values' => null,
                'new_values' => json_encode([
                    'formato' => $request->formato ?? 'pdf',
                    'filtros' => $request->except(['_token', 'formato']),
                    'total_registros' => $movimentos->count()
                ]),
                'url' => $request->url(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);

            $formato = $request->get('formato', 'pdf');
            $filename = 'relatorio_movimentos_' . date('Y-m-d_H-i-s');

            if ($formato === 'excel') {
                return $this->generateExcelReport($movimentos, $filename);
            } else {
                return $this->generatePdfReport($movimentos, $filename, $request);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório de movimentos: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar relatório.');
        }
    }

    /**
     * Gerar relatório PDF
     */
    private function generatePdfReport($movimentos, $filename, $request)
    {
        // Obter configurações do sistema
        $configuracoes = DB::table('configuracoes')->first();
        $logo = $configuracoes->logo ?? null;
        $cabecalho = $configuracoes->cabecalho_documentos ?? 'Sistema de Gestão Portuária';
        $rodape = $configuracoes->rodape_documentos ?? '';

        // Decodificar cabeçalho se for JSON
        if (is_string($cabecalho) && json_decode($cabecalho)) {
            $cabecalho = json_decode($cabecalho, true);
            $cabecalho = $cabecalho['texto'] ?? 'Sistema de Gestão Portuária';
        }

        $data = [
            'movimentos' => $movimentos,
            'logo' => $logo,
            'cabecalho' => $cabecalho,
            'rodape' => $rodape,
            'filtros' => $request->except(['_token', 'formato']),
            'data_geracao' => now()->format('d/m/Y H:i:s'),
            'usuario' => Auth::user()->name
        ];

        $pdf = PDF::loadView('concessionarias.entrada-saida.relatorio-pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'Arial'
        ]);

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Gerar relatório Excel
     */
    private function generateExcelReport($movimentos, $filename)
    {
        return $this->generateExcelCsv($movimentos, $filename);
    }

    /**
     * Gerar CSV para Excel
     */
    private function generateExcelCsv($movimentos, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
            'Cache-Control' => 'max-age=0',
        ];

        $callback = function() use ($movimentos) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Cabeçalhos
            fputcsv($file, [
                'Número Movimento', 'Embarcação', 'IMO', 'Concessionária', 'Terminal',
                'Tipo Movimento', 'Data Movimento', 'Hora Prevista', 'Hora Real',
                'Status', 'Usuário', 'Autorizado Por', 'Data Criação'
            ], ';');

            // Dados
            foreach ($movimentos as $movimento) {
                fputcsv($file, [
                    $movimento->numero_movimento,
                    $movimento->embarcacaoConcessionaria->nome ?? '',
                    $movimento->embarcacaoConcessionaria->imo ?? '',
                    $movimento->concessionaria->nome ?? '',
                    $movimento->terminal->nome ?? '',
                    ucfirst($movimento->tipo_movimento),
                    $movimento->data_movimento ? Carbon::parse($movimento->data_movimento)->format('d/m/Y') : '',
                    $movimento->hora_prevista ? Carbon::parse($movimento->hora_prevista)->format('H:i') : '',
                    $movimento->hora_real ? Carbon::parse($movimento->hora_real)->format('H:i') : '',
                    ucfirst($movimento->status),
                    $movimento->usuario->name ?? '',
                    $movimento->autorizadoPor->name ?? '',
                    $movimento->created_at->format('d/m/Y H:i:s')
                ], ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}


/**
 * Revogar movimento concluído para edição (apenas admin)
 */
