<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ProdutoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:produtos.view')->only(['index', 'show']);
        $this->middleware('permission:produtos.create')->only(['create', 'store']);
        $this->middleware('permission:produtos.edit')->only(['edit', 'update']);
        $this->middleware('permission:produtos.delete')->only(['destroy']);
    }

    /**
     * Display a listing of produtos
     */
    public function index(Request $request)
    {
        try {
            $query = Produto::query()
                ->when($request->search, function ($q) use ($request) {
                    $q->where(function ($query) use ($request) {
                        $query->where('nome', 'like', '%' . $request->search . '%')
                            ->orWhere('codigo', 'like', '%' . $request->search . '%')
                            ->orWhere('ncm', 'like', '%' . $request->search . '%')
                            ->orWhere('descricao', 'like', '%' . $request->search . '%');
                    });
                })
                ->when($request->categoria, function ($q) use ($request) {
                    $q->where('categoria', $request->categoria);
                })
                ->when($request->unidade_medida, function ($q) use ($request) {
                    $q->where('unidade_medida', $request->unidade_medida);
                })
                ->when($request->status !== null, function ($q) use ($request) {
                    $q->where('is_active', $request->status);
                });

            $produtos = $query->withCount(['movimentosTerminais', 'movimentosProdutos'])
                ->orderBy($request->get('sort', 'nome'), $request->get('direction', 'asc'))
                ->paginate($request->get('per_page', 15))
                ->withQueryString();

            $categorias = Produto::select('categoria')
                ->distinct()
                ->whereNotNull('categoria')
                ->orderBy('categoria')
                ->pluck('categoria');

            $unidades = Produto::select('unidade_medida')
                ->distinct()
                ->whereNotNull('unidade_medida')
                ->orderBy('unidade_medida')
                ->pluck('unidade_medida');

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'view',
                'auditable_type' => 'App\\Models\\Produto',
                'description' => 'Visualizou listagem de produtos',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return view('produtos.index', compact('produtos', 'categorias', 'unidades'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar produtos: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new produto
     */
    public function create()
    {
        try {
            return view('produtos.create');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar formulário: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created produto
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'codigo' => 'required|string|unique:produtos,codigo|max:50',
                'ncm' => 'nullable|string|max:20',
                'categoria' => 'required|in:' . implode(',', Produto::CATEGORIAS),
                'descricao' => 'nullable|string|max:1000',
                'unidade_medida' => 'required|in:' . implode(',', Produto::UNIDADES_MEDIDA),
                'peso_especifico' => 'nullable|numeric|min:0|max:9999.99',
                'densidade' => 'nullable|numeric|min:0|max:9999.99',
                'temperatura_armazenamento' => 'nullable|numeric|min:-50|max:100',
                'umidade_maxima' => 'nullable|numeric|min:0|max:100',
                'observacoes' => 'nullable|string|max:1000',
                'is_active' => 'boolean'
            ], [
                'nome.required' => 'O nome do produto é obrigatório.',
                'codigo.required' => 'O código do produto é obrigatório.',
                'codigo.unique' => 'Este código já está cadastrado.',
                'categoria.required' => 'A categoria é obrigatória.',
                'categoria.in' => 'Categoria inválida.',
                'unidade_medida.required' => 'A unidade de medida é obrigatória.',
                'unidade_medida.in' => 'Unidade de medida inválida.'
            ]);

            DB::beginTransaction();

            $produto = Produto::create($validated);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'model_type' => 'Produto',
                'model_id' => $produto->id,
                'description' => "Criou produto: {$produto->nome} (Código: {$produto->codigo})",
                'changes' => json_encode($validated),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route('produtos.index')
                ->with('success', 'Produto cadastrado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao cadastrar produto: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified produto
     */
    public function show(Produto $produto)
    {
        try {
            $produto->load([
                'movimentosTerminais' => function ($query) {
                    $query->with(['terminal'])->latest()->take(10);
                },
                'movimentosProdutos' => function ($query) {
                    $query->latest()->take(10);
                }
            ]);

            // Estatísticas do produto
            $stats = [
                'movimentos_mes' => $produto->movimentosTerminais()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'quantidade_movimentada_mes' => $produto->movimentosTerminais()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('quantidade'),
                'terminais_utilizados' => $produto->movimentosTerminais()
                    ->distinct('terminal_id')
                    ->count(),
                'ultimo_movimento' => $produto->movimentosTerminais()
                    ->latest()
                    ->first()?->created_at
            ];

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'view',
                'model_type' => 'Produto',
                'model_id' => $produto->id,
                'description' => "Visualizou produto: {$produto->nome}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return view('produtos.show', compact('produto', 'stats'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar produto: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified produto
     */
    public function edit(Produto $produto)
    {
        try {
            return view('produtos.edit', compact('produto'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar formulário: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified produto
     */
    public function update(Request $request, Produto $produto)
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'codigo' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('produtos', 'codigo')->ignore($produto->id)
                ],
                'ncm' => 'nullable|string|max:20',
                'categoria' => 'required|in:' . implode(',', Produto::CATEGORIAS),
                'descricao' => 'nullable|string|max:1000',
                'unidade_medida' => 'required|in:' . implode(',', Produto::UNIDADES_MEDIDA),
                'peso_especifico' => 'nullable|numeric|min:0|max:9999.99',
                'densidade' => 'nullable|numeric|min:0|max:9999.99',
                'temperatura_armazenamento' => 'nullable|numeric|min:-50|max:100',
                'umidade_maxima' => 'nullable|numeric|min:0|max:100',
                'observacoes' => 'nullable|string|max:1000',
                'is_active' => 'boolean'
            ], [
                'nome.required' => 'O nome do produto é obrigatório.',
                'codigo.required' => 'O código do produto é obrigatório.',
                'codigo.unique' => 'Este código já está cadastrado.',
                'categoria.required' => 'A categoria é obrigatória.',
                'categoria.in' => 'Categoria inválida.',
                'unidade_medida.required' => 'A unidade de medida é obrigatória.',
                'unidade_medida.in' => 'Unidade de medida inválida.'
            ]);

            DB::beginTransaction();

            $originalData = $produto->toArray();
            $produto->update($validated);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'model_type' => 'Produto',
                'model_id' => $produto->id,
                'description' => "Atualizou produto: {$produto->nome}",
                'changes' => json_encode([
                    'before' => $originalData,
                    'after' => $validated
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route('produtos.index')
                ->with('success', 'Produto atualizado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar produto: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified produto
     */
    public function destroy(Produto $produto)
    {
        try {
            DB::beginTransaction();

            // Verificar se há movimentos associados
            if ($produto->movimentosTerminais()->exists() || $produto->movimentosProdutos()->exists()) {
                return back()->with('error', 'Não é possível excluir este produto pois possui movimentos associados.');
            }

            $nome = $produto->nome;
            $produto->delete();

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'delete',
                'auditable_type' => 'App\\Models\\Produto',
                'auditable_id' => $produto->id,
                'description' => "Excluiu produto: {$produto->nome}",
                'old_values' => $produto->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return redirect()->route('produtos.index')
                ->with('success', 'Produto excluído com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao excluir produto: ' . $e->getMessage());
        }
    }

    /**
     * Toggle produto status
     */
    public function toggleStatus(Produto $produto)
    {
        try {
            DB::beginTransaction();

            $produto->update(['is_active' => !$produto->is_active]);
            $status = $produto->is_active ? 'ativado' : 'desativado';

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'App\\Models\\Produto',
                'auditable_id' => $produto->id,
                'description' => "Produto {$produto->nome} foi {$status}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return back()->with('success', "Produto {$status} com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao alterar status: ' . $e->getMessage());
        }
    }

    /**
     * Get produtos for API/AJAX
     */
    public function api(Request $request)
    {
        try {
            $produtos = Produto::query()
                ->when($request->search, function ($q) use ($request) {
                    $q->where('nome', 'like', '%' . $request->search . '%')
                        ->orWhere('codigo', 'like', '%' . $request->search . '%');
                })
                ->when($request->categoria, function ($q) use ($request) {
                    $q->where('categoria', $request->categoria);
                })
                ->ativas()
                ->orderBy('nome')
                ->limit(50)
                ->get()
                ->map(function ($produto) {
                    return [
                        'id' => $produto->id,
                        'nome' => $produto->nome,
                        'codigo' => $produto->codigo,
                        'categoria' => $produto->categoria,
                        'unidade_medida' => $produto->unidade_medida,
                        'ncm' => $produto->ncm
                    ];
                });

            return response()->json($produtos);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar produtos'], 500);
        }
    }
}