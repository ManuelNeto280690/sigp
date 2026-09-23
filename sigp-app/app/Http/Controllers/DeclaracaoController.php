<?php

namespace App\Http\Controllers;

use App\Models\Declaracao;
use App\Models\Embarcacao;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeclaracaoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:declaracoes.view')->only(['index', 'show']);
        $this->middleware('permission:declaracoes.create')->only(['create', 'store']);
        $this->middleware('permission:declaracoes.edit')->only(['edit', 'update']);
        $this->middleware('permission:declaracoes.delete')->only(['destroy']);
        $this->middleware('permission:declaracoes.approve')->only(['approve', 'reject']);
    }

    /**
     * Display a listing of declaracoes
     */
    public function index(Request $request)
    {
        try {
            $query = Declaracao::with(['embarcacao.concessionaria', 'usuario'])
                ->when($request->search, function ($q) use ($request) {
                    $q->where(function ($query) use ($request) {
                        $query->where('numero_declaracao', 'like', '%' . $request->search . '%')
                            ->orWhere('descricao_carga', 'like', '%' . $request->search . '%')
                            ->orWhereHas('embarcacao', function ($q) use ($request) {
                                $q->where('nome', 'like', '%' . $request->search . '%')
                                    ->orWhere('imo', 'like', '%' . $request->search . '%');
                            });
                    });
                })
                ->when($request->status, function ($q) use ($request) {
                    $q->where('status', $request->status);
                })
                ->when($request->tipo_operacao, function ($q) use ($request) {
                    $q->where('tipo_operacao', $request->tipo_operacao);
                })
                ->when($request->embarcacao_id, function ($q) use ($request) {
                    $q->where('embarcacao_id', $request->embarcacao_id);
                })
                ->when($request->data_inicio && $request->data_fim, function ($q) use ($request) {
                    $q->whereBetween('data_operacao', [
                        Carbon::parse($request->data_inicio)->startOfDay(),
                        Carbon::parse($request->data_fim)->endOfDay()
                    ]);
                });

            $declaracoes = $query->orderBy($request->get('sort', 'created_at'), $request->get('direction', 'desc'))
                ->paginate($request->get('per_page', 15))
                ->withQueryString();

            $embarcacoes = Embarcacao::ativas()->with('concessionaria')->orderBy('nome')->get();

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'view',
                'model_type' => 'Declaracao',
                'description' => 'Visualizou listagem de declarações',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return view('declaracoes.index', compact('declaracoes', 'embarcacoes'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar declarações: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new declaracao
     */
    public function create()
    {
        try {
            $embarcacoes = Embarcacao::ativas()->with('concessionaria')->orderBy('nome')->get();
            
            return view('declaracoes.create', compact('embarcacoes'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar formulário: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created declaracao
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'embarcacao_id' => 'required|exists:embarcacoes,id',
                'numero_declaracao' => 'required|string|unique:declaracoes,numero_declaracao|max:50',
                'tipo_operacao' => 'required|in:carga,descarga,abastecimento',
                'data_operacao' => 'required|date|after_or_equal:today',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fim' => 'required|date_format:H:i|after:hora_inicio',
                'descricao_carga' => 'required|string|max:1000',
                'quantidade_carga' => 'required|numeric|min:0.01|max:999999.99',
                'unidade_medida' => 'required|string|max:20',
                'porto_origem' => 'nullable|string|max:255',
                'porto_destino' => 'nullable|string|max:255',
                'observacoes' => 'nullable|string|max:1000'
            ], [
                'embarcacao_id.required' => 'A embarcação é obrigatória.',
                'embarcacao_id.exists' => 'Embarcação não encontrada.',
                'numero_declaracao.required' => 'O número da declaração é obrigatório.',
                'numero_declaracao.unique' => 'Este número de declaração já existe.',
                'tipo_operacao.required' => 'O tipo de operação é obrigatório.',
                'tipo_operacao.in' => 'Tipo de operação inválido.',
                'data_operacao.required' => 'A data da operação é obrigatória.',
                'data_operacao.after_or_equal' => 'A data da operação deve ser hoje ou futura.',
                'hora_inicio.required' => 'A hora de início é obrigatória.',
                'hora_fim.required' => 'A hora de fim é obrigatória.',
                'hora_fim.after' => 'A hora de fim deve ser posterior à hora de início.',
                'descricao_carga.required' => 'A descrição da carga é obrigatória.',
                'quantidade_carga.required' => 'A quantidade da carga é obrigatória.',
                'unidade_medida.required' => 'A unidade de medida é obrigatória.'
            ]);

            DB::beginTransaction();

            $validated['user_id'] = Auth::id();
            $validated['status'] = 'pendente';
            
            $declaracao = Declaracao::create($validated);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'model_type' => 'Declaracao',
                'model_id' => $declaracao->id,
                'description' => "Criou declaração: {$declaracao->numero_declaracao}",
                'changes' => json_encode($validated),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route('declaracoes.index')
                ->with('success', 'Declaração cadastrada com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao cadastrar declaração: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified declaracao
     */
    public function show(Declaracao $declaracao)
    {
        try {
            $declaracao->load(['embarcacao.concessionaria', 'usuario', 'aprovadoPor']);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'view',
                'model_type' => 'Declaracao',
                'model_id' => $declaracao->id,
                'description' => "Visualizou declaração: {$declaracao->numero_declaracao}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return view('declaracoes.show', compact('declaracao'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar declaração: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified declaracao
     */
    public function edit(Declaracao $declaracao)
    {
        try {
            // Só permite editar se estiver pendente
            if ($declaracao->status !== 'pendente') {
                return back()->with('error', 'Só é possível editar declarações pendentes.');
            }

            $embarcacoes = Embarcacao::ativas()->with('concessionaria')->orderBy('nome')->get();
            
            return view('declaracoes.edit', compact('declaracao', 'embarcacoes'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar formulário: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified declaracao
     */
    public function update(Request $request, Declaracao $declaracao)
    {
        try {
            // Só permite editar se estiver pendente
            if ($declaracao->status !== 'pendente') {
                return back()->with('error', 'Só é possível editar declarações pendentes.');
            }

            $validated = $request->validate([
                'embarcacao_id' => 'required|exists:embarcacoes,id',
                'tipo_operacao' => 'required|in:carga,descarga,abastecimento',
                'data_operacao' => 'required|date|after_or_equal:today',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fim' => 'required|date_format:H:i|after:hora_inicio',
                'descricao_carga' => 'required|string|max:1000',
                'quantidade_carga' => 'required|numeric|min:0.01|max:999999.99',
                'unidade_medida' => 'required|string|max:20',
                'porto_origem' => 'nullable|string|max:255',
                'porto_destino' => 'nullable|string|max:255',
                'observacoes' => 'nullable|string|max:1000'
            ], [
                'embarcacao_id.required' => 'A embarcação é obrigatória.',
                'tipo_operacao.required' => 'O tipo de operação é obrigatório.',
                'data_operacao.required' => 'A data da operação é obrigatória.',
                'data_operacao.after_or_equal' => 'A data da operação deve ser hoje ou futura.',
                'hora_fim.after' => 'A hora de fim deve ser posterior à hora de início.'
            ]);

            DB::beginTransaction();

            $originalData = $declaracao->toArray();
            $declaracao->update($validated);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'model_type' => 'Declaracao',
                'model_id' => $declaracao->id,
                'description' => "Atualizou declaração: {$declaracao->numero_declaracao}",
                'changes' => json_encode([
                    'before' => $originalData,
                    'after' => $validated
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route('declaracoes.index')
                ->with('success', 'Declaração atualizada com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar declaração: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified declaracao
     */
    public function destroy(Declaracao $declaracao)
    {
        try {
            // Só permite excluir se estiver pendente
            if ($declaracao->status !== 'pendente') {
                return back()->with('error', 'Só é possível excluir declarações pendentes.');
            }

            DB::beginTransaction();

            $numero = $declaracao->numero_declaracao;
            $declaracao->delete();

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'delete',
                'model_type' => 'Declaracao',
                'model_id' => $declaracao->id,
                'description' => "Excluiu declaração: {$numero}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return redirect()->route('declaracoes.index')
                ->with('success', 'Declaração excluída com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao excluir declaração: ' . $e->getMessage());
        }
    }

    /**
     * Approve declaracao
     */
    public function approve(Declaracao $declaracao)
    {
        try {
            if ($declaracao->status !== 'pendente') {
                return back()->with('error', 'Esta declaração não pode ser aprovada.');
            }

            DB::beginTransaction();

            $declaracao->update([
                'status' => 'aprovada',
                'aprovado_por' => Auth::id(),
                'aprovado_em' => now()
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'approve',
                'model_type' => 'Declaracao',
                'model_id' => $declaracao->id,
                'description' => "Aprovou declaração: {$declaracao->numero_declaracao}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return back()->with('success', 'Declaração aprovada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao aprovar declaração: ' . $e->getMessage());
        }
    }

    /**
     * Reject declaracao
     */
    public function reject(Request $request, Declaracao $declaracao)
    {
        try {
            if ($declaracao->status !== 'pendente') {
                return back()->with('error', 'Esta declaração não pode ser rejeitada.');
            }

            $request->validate([
                'motivo_rejeicao' => 'required|string|max:1000'
            ], [
                'motivo_rejeicao.required' => 'O motivo da rejeição é obrigatório.'
            ]);

            DB::beginTransaction();

            $declaracao->update([
                'status' => 'rejeitada',
                'aprovado_por' => Auth::id(),
                'aprovado_em' => now(),
                'observacoes' => ($declaracao->observacoes ? $declaracao->observacoes . "\n\n" : '') . 
                    "REJEITADA EM " . now()->format('d/m/Y H:i') . " POR " . Auth::user()->name . ":\n" . 
                    $request->motivo_rejeicao
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'reject',
                'model_type' => 'Declaracao',
                'model_id' => $declaracao->id,
                'description' => "Rejeitou declaração: {$declaracao->numero_declaracao}",
                'changes' => json_encode(['motivo' => $request->motivo_rejeicao]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return back()->with('success', 'Declaração rejeitada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao rejeitar declaração: ' . $e->getMessage());
        }
    }
}