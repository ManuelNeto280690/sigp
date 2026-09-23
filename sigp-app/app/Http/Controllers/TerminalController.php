<?php

namespace App\Http\Controllers;

use App\Models\Terminal;
use App\Models\Concessionaria;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;



class TerminalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:terminais.view')->only(['index', 'show']);
        $this->middleware('permission:terminais.create')->only(['create', 'store']);
        $this->middleware('permission:terminais.edit')->only(['edit', 'update']);
        $this->middleware('permission:terminais.delete')->only(['destroy']);
    }

    /**
     * Display a listing of terminais
     */
    public function index(Request $request)
    {
        try {
            $query = Terminal::query()
                ->when($request->search, function ($q) use ($request) {
                    $q->where(function ($query) use ($request) {
                        $query->where('nome', 'like', '%' . $request->search . '%')
                            ->orWhere('codigo', 'like', '%' . $request->search . '%')
                            ->orWhere('localizacao', 'like', '%' . $request->search . '%')
                            ->orWhere('responsavel', 'like', '%' . $request->search . '%');
                    });
                })
                ->when($request->status !== null, function ($q) use ($request) {
                    $q->where('is_active', $request->status);
                })
                ->when($request->tipo, function ($q) use ($request) {
                    $q->where('tipo', $request->tipo);
                });
    
            $terminais = $query->withCount(['movimentosTerminais', 'entradasSaidas'])
                ->orderBy($request->get('sort', 'nome'), $request->get('direction', 'asc'))
                ->paginate($request->get('per_page', 15))
                ->withQueryString();

            $tipos = Terminal::select('tipo')
                ->distinct()
                ->whereNotNull('tipo')
                ->orderBy('tipo')
                ->pluck('tipo');

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'view',
                'auditable_type' => 'Terminal',
                'description' => 'Visualizou listagem de terminais',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'created_at' => now()
            ]);

            return view('terminais.index', compact('terminais', 'tipos'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar terminais: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new terminal
     */
    public function create()
    {
        try {
            $concessionarias = Concessionaria::ativo()->orderBy('nome')->get();
            
            // Gerar próximo código para exibição
            $proximoCodigo = \App\Models\Terminal::gerarCodigo();
            
            return view('terminais.create', compact('concessionarias', 'proximoCodigo'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar formulário: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created terminal
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'concessionaria_id' => 'required|uuid|exists:concessionarias,id',
                'nome' => 'required|string|max:255',
                'codigo' => 'nullable|string|unique:terminais,codigo|max:20', // Mudado para nullable
                'tipo' => 'required|string',
                'descricao' => 'nullable|string|max:1000',
                'area_total' => 'required|numeric|min:0|max:999999.99',
                'area_operacional' => 'required|numeric|min:0|max:999999.99',
                'numero_bercos' => 'required|integer|min:1|max:99',
                'calado_maximo' => 'required|numeric|min:0|max:99.99',
                'capacidade_armazenagem' => 'required|integer|min:1|max:999999999',
                'equipamentos' => 'nullable|array',
                'status' => 'required|in:ativo,inativo,manutencao',
                'observacoes' => 'nullable|string|max:1000',
                'is_active' => 'boolean'
            ], [
                'concessionaria_id.required' => 'A concessionária é obrigatória.',
                'concessionaria_id.exists' => 'A concessionária selecionada não existe.',
                'nome.required' => 'O nome do terminal é obrigatório.',
                'codigo.unique' => 'Este código já está cadastrado.',
                'tipo.required' => 'O tipo do terminal é obrigatório.',
                'area_total.required' => 'A área total é obrigatória.',
                'area_operacional.required' => 'A área operacional é obrigatória.',
                'numero_bercos.required' => 'O número de berços é obrigatório.',
                'calado_maximo.required' => 'O calado máximo é obrigatório.',
                'capacidade_armazenagem.required' => 'A capacidade de armazenagem é obrigatória.',
                'status.required' => 'O status é obrigatório.',
                'status.in' => 'Status inválido.'
            ]);

            DB::beginTransaction();

            $terminal = Terminal::create($validated);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'create',
                'auditable_type' => 'Terminal',
                'auditable_id' => $terminal->id,
                'description' => "Criou terminal: {$terminal->nome} (Código: {$terminal->codigo})",
                'old_values' => null,
                'new_values' => json_encode($validated),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'created_at' => now()
            ]);

            DB::commit();

            return redirect()->route('terminais.index')
                ->with('success', 'Terminal cadastrado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao cadastrar terminal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified terminal
     */
    public function show($id)
    {
        try {
            // Buscar o terminal explicitamente pelo ID
            $terminal = Terminal::with('concessionaria')->findOrFail($id);

            // Debug: verificar se o terminal tem dados
            Log::info('Terminal carregado:', [
                'id' => $terminal->id,
                'nome' => $terminal->nome,
                'codigo' => $terminal->codigo,
                'tipo' => $terminal->tipo,
                'descricao' => $terminal->descricao,
                'is_active' => $terminal->is_active,
                'attributes' => $terminal->getAttributes()
            ]);

            // Estatísticas simples
            $stats = [
                'movimentos_mes' => 0,
                'embarcacoes_mes' => 0,
                'movimentos_concluidos' => 0,
                'movimentos_ativos' => 0,
                'capacidade_utilizada' => 0,
                'percentual_ocupacao' => 0
            ];

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'view',
                'auditable_type' => 'Terminal',
                'auditable_id' => $terminal->id,
                'description' => "Visualizou terminal: {$terminal->nome}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'created_at' => now()
            ]);

            return view('terminais.show', compact('terminal', 'stats'));

        } catch (\Exception $e) {
            Log::error('Erro ao carregar terminal: ' . $e->getMessage(), [
                'terminal_id' => $id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Erro ao carregar terminal: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified terminal
     */
    public function edit($id, Request $request)
    {
        try {
            $terminal = Terminal::findOrFail($id);
            $concessionarias = Concessionaria::ativo()->orderBy('nome')->get();
    
            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'view',
                'auditable_type' => 'Terminal',
                'description' => 'Visualizou formulário de edição do terminal: ' . $terminal->nome,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'created_at' => now()
            ]);
    
            return view('terminais.edit', compact('terminal', 'concessionarias'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar terminal: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified terminal
     */
    public function update(Request $request, $id)
    {
        try {
            $terminal = Terminal::findOrFail($id);
            
            $validated = $request->validate([
                'concessionaria_id' => 'required|exists:concessionarias,id',
                'nome' => 'required|string|max:255',
                // Removido a validação do código pois não será mais editável
                'tipo' => 'required|string',
                'descricao' => 'nullable|string',
                'area_total' => 'nullable|numeric|min:0',
                'area_operacional' => 'nullable|numeric|min:0',
                'numero_bercos' => 'required|integer|min:1|max:50',
                'calado_maximo' => 'nullable|numeric|min:0',
                'capacidade_armazenagem' => 'nullable|integer|min:0',
                'equipamentos' => 'nullable|array',
                'status' => 'required|in:ativo,inativo,manutencao',
                'observacoes' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            // Remover o código dos dados validados para não tentar atualizá-lo
            unset($validated['codigo']);

            $terminal->update($validated);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'Terminal',
                'auditable_id' => $terminal->id,
                'description' => "Atualizou terminal: {$terminal->nome}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'created_at' => now()
            ]);

            return redirect()->route('terminais.show', $terminal->id)
                ->with('success', 'Terminal atualizado com sucesso!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao atualizar terminal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified terminal from storage
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $terminal = Terminal::findOrFail($id);
            
            // Verificar se há movimentos associados
            if ($terminal->movimentosTerminais()->exists()) {
                return back()->with('error', 'Não é possível excluir este terminal pois possui movimentos associados.');
            }
    
            // Verificar se há entradas/saídas de embarcações associadas
            if ($terminal->entradasSaidas()->exists()) {
                return back()->with('error', 'Não é possível excluir este terminal pois possui registros de embarcações associados.');
            }
            
            $nome = $terminal->nome;
            
            // Log da ação antes de deletar
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'delete',
                'auditable_type' => 'Terminal',
                'auditable_id' => $terminal->id,
                'description' => "Excluiu terminal: {$nome}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'created_at' => now()
            ]);
    
            $terminal->delete();
            
            DB::commit();
    
            return redirect()->route('terminais.index')
                ->with('success', 'Terminal excluído com sucesso!');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao excluir terminal: ' . $e->getMessage());
        }
    }

    /**
     * Toggle terminal status
     */
    public function toggleStatus($id)
    {
        try {
            DB::beginTransaction();

            $terminal = Terminal::findOrFail($id);
            $terminal->update(['is_active' => !$terminal->is_active]);
            $status = $terminal->is_active ? 'ativado' : 'desativado';

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'updated',
                'auditable_type' => 'Terminal',
                'auditable_id' => $terminal->id,
                'description' => "Terminal {$terminal->nome} foi {$status}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'created_at' => now()
            ]);

            DB::commit();

            return back()->with('success', "Terminal {$status} com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao alterar status: ' . $e->getMessage());
        }
    }

    /**
     * Get terminais for API/AJAX
     */
    public function api(Request $request)
    {
        try {
            $terminais = Terminal::query()
                ->when($request->search, function ($q) use ($request) {
                    $q->where('nome', 'like', '%' . $request->search . '%')
                        ->orWhere('codigo', 'like', '%' . $request->search . '%');
                })
                ->when($request->tipo, function ($q) use ($request) {
                    $q->where('tipo', $request->tipo);
                })
                ->ativas()
                ->orderBy('nome')
                ->limit(50)
                ->get()
                ->map(function ($terminal) {
                    return [
                        'id' => $terminal->id,
                        'nome' => $terminal->nome,
                        'codigo' => $terminal->codigo,
                        'tipo' => $terminal->tipo,
                        'localizacao' => $terminal->localizacao,
                        'bercos_disponiveis' => $terminal->bercos_disponiveis
                    ];
                });

            return response()->json($terminais);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar terminais'], 500);
        }
    }

    /**
     * Generate terminals report
     */
    public function relatorioFiltrado(Request $request)
    {
        try {
            $query = Terminal::with(['concessionaria']);

            // Aplicar filtros
            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('nome', 'like', '%' . $request->search . '%')
                        ->orWhere('codigo', 'like', '%' . $request->search . '%')
                        ->orWhere('localizacao', 'like', '%' . $request->search . '%')
                        ->orWhere('responsavel', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->status !== null) {
                $query->where('is_active', $request->status);
            }

            if ($request->tipo) {
                $query->where('tipo', $request->tipo);
            }

            $terminais = $query->orderBy('nome')->get();

            // Preparar filtros aplicados
            $filtrosAplicados = [];
            if ($request->search) {
                $filtrosAplicados['Busca'] = $request->search;
            }
            if ($request->status !== null) {
                $filtrosAplicados['Status'] = $request->status ? 'Ativo' : 'Inativo';
            }
            if ($request->tipo) {
                $filtrosAplicados['Tipo'] = $request->tipo;
            }

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'report',
                'auditable_type' => 'Terminal',
                'description' => 'Gerou relatório de terminais',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'created_at' => now()
            ]);

            // Verificar formato solicitado
            if ($request->formato === 'pdf') {
                return $this->generatePdfReport($terminais, $filtrosAplicados);
            } elseif ($request->formato === 'excel') {
                return $this->generateExcelReport($terminais, $filtrosAplicados);
            }

            // Retornar view padrão se não especificado formato
            return view('terminais.relatorio', compact('terminais', 'filtrosAplicados'));

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório de terminais: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF report
     */
    private function generatePdfReport($terminais, $filtrosAplicados)
    {
        try {
            $configuracoes = config('relatorios.cabecalho_documentos', [
                'cabecalho' => [
                    'titulo' => 'PORTO DE SOYO',
                    'subtitulo' => 'Sistema Integrado de Gestão Portuária'
                ]
            ]);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('terminais.relatorio-pdf', compact('terminais', 'filtrosAplicados', 'configuracoes'));
            
            $filename = 'relatorio_terminais_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF de terminais: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate Excel report
     */
    private function generateExcelReport($terminais, $filtrosAplicados)
    {
        try {
            $filename = 'relatorio_terminais_' . now()->format('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ];

            $callback = function() use ($terminais, $filtrosAplicados) {
                $file = fopen('php://output', 'w');
                
                // BOM para UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Cabeçalho do relatório
                fputcsv($file, ['RELATÓRIO DE TERMINAIS'], ';');
                fputcsv($file, ['Data de Geração: ' . now()->format('d/m/Y H:i:s')], ';');
                fputcsv($file, ['Total de Registros: ' . $terminais->count()], ';');
                fputcsv($file, ['Gerado por: ' . Auth::user()->name], ';');
                fputcsv($file, [''], ';'); // Linha em branco
                
                // Filtros aplicados
                if (!empty($filtrosAplicados)) {
                    fputcsv($file, ['FILTROS APLICADOS:'], ';');
                    foreach ($filtrosAplicados as $filtro => $valor) {
                        fputcsv($file, [$filtro . ': ' . $valor], ';');
                    }
                    fputcsv($file, [''], ';'); // Linha em branco
                }
                
                // Cabeçalhos das colunas
                fputcsv($file, [
                    'Nome',
                    'Código',
                    'Tipo',
                    'Localização',
                    'Concessionária',
                    'Número de Berços',
                    'Calado Máximo',
                    'Capacidade Armazenagem',
                    'Status',
                    'Data Cadastro'
                ], ';');
                
                // Dados dos terminais
                foreach ($terminais as $terminal) {
                    fputcsv($file, [
                        $terminal->nome,
                        $terminal->codigo,
                        $terminal->tipo,
                        $terminal->localizacao,
                        $terminal->concessionaria ? $terminal->concessionaria->nome : 'Não definida',
                        $terminal->numero_bercos,
                        $terminal->calado_maximo ? $terminal->calado_maximo . 'm' : 'N/A',
                        $terminal->capacidade_armazenagem ? number_format($terminal->capacidade_armazenagem, 0, ',', '.') : 'N/A',
                        $terminal->is_active ? 'Ativo' : 'Inativo',
                        $terminal->created_at->format('d/m/Y H:i')
                    ], ';');
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar Excel de terminais: ' . $e->getMessage());
            throw $e;
        }
    }
}