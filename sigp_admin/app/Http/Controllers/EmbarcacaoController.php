<?php

namespace App\Http\Controllers;

use App\Models\Embarcacao;
use App\Models\Concessionaria;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;



class EmbarcacaoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
       $this->middleware('permission:embarcacoes.view')->only(['index', 'show']);
        $this->middleware('permission:embarcacoes.create')->only(['create', 'store']);
        $this->middleware('permission:embarcacoes.edit')->only(['edit', 'update']);
        $this->middleware('permission:embarcacoes.delete')->only(['destroy']);
        $this->middleware('permission:embarcacoes.export')->only(['export']);
        $this->middleware('permission:embarcacoes.import')->only(['import', 'processImport']);
    }

    /**
     * Display a listing of embarcacoes
     */
    public function index(Request $request)
    {
        try {
            // Log da ação no início
            Log::info('EmbarcacaoController@index: Iniciando listagem de embarcações', [
                'user_id' => Auth::id(),
                'filters' => $request->only(['search', 'status', 'tipo_embarcacao', 'bandeira', 'data_inicio', 'data_fim']),
                'ip' => $request->ip()
            ]);

            $query = Embarcacao::query()
                ->when($request->search, function ($q) use ($request) {
                    $q->where(function ($query) use ($request) {
                        $query->where('nome', 'like', '%' . $request->search . '%')
                            ->orWhere('imo', 'like', '%' . $request->search . '%')
                            ->orWhere('mmsi', 'like', '%' . $request->search . '%')
                            ->orWhere('bandeira', 'like', '%' . $request->search . '%');
                    });
                })
                ->when($request->status, function ($q) use ($request) {
                    $q->where('status', $request->status);
                })
                ->when($request->tipo_embarcacao, function ($q) use ($request) {
                    $q->where('tipo_embarcacao', $request->tipo_embarcacao);
                })
                ->when($request->bandeira, function ($q) use ($request) {
                    $q->where('bandeira', $request->bandeira);
                })
                ->when($request->data_inicio && $request->data_fim, function ($q) use ($request) {
                    $q->whereBetween('created_at', [
                        Carbon::parse($request->data_inicio)->startOfDay(),
                        Carbon::parse($request->data_fim)->endOfDay()
                    ]);
                });

            $embarcacoes = $query->orderBy($request->get('sort', 'nome'), $request->get('direction', 'asc'))
                ->paginate($request->get('per_page', 15))
                ->withQueryString();

            // Obter dados para filtros
            $tipos = Embarcacao::select('tipo_embarcacao')
                ->distinct()
                ->whereNotNull('tipo_embarcacao')
                ->orderBy('tipo_embarcacao')
                ->pluck('tipo_embarcacao');
            $bandeiras = Embarcacao::select('bandeira')
                ->distinct()
                ->whereNotNull('bandeira')
                ->orderBy('bandeira')
                ->pluck('bandeira');

            // Estatísticas para a view
            $stats = [
                'total' => Embarcacao::count(),
                'ativas' => Embarcacao::where('is_active', true)->count(),
                'inativas' => Embarcacao::where('is_active', false)->count(),
                'atracadas' => Embarcacao::where('status', 'atracado')->count(),
                'operando' => Embarcacao::where('status', 'operando')->count(),
                'esperadas' => Embarcacao::where('status', 'esperado')->count(),
                'partidas' => Embarcacao::where('status', 'partido')->count(),
                'no_porto' => Embarcacao::where('status', 'atracado')->count()
            ];

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'view',
                'auditable_type' => 'App\\Models\\Embarcacao',
                'auditable_id' => null,
                'url' => $request->url(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return view('embarcacoes.index', compact(
                'embarcacoes', 'stats', 'tipos', 'bandeiras'
            ));
        } catch (\Exception $e) {
            Log::error('EmbarcacaoController@index: Erro ao carregar embarcações', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'ip' => $request->ip()
            ]);
            return back()->with('error', 'Erro ao carregar embarcações: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new embarcacao
     */
    public function create()
    {
        try {
            return view('embarcacoes.create');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar formulário: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created embarcacao
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
               
                'nome' => 'required|string|max:255',
                'imo' => 'required|string|unique:embarcacoes,imo|max:20',
                'mmsi' => 'nullable|string|unique:embarcacoes,mmsi|max:20',
                'bandeira' => 'required|string|max:100',
                'tipo_embarcacao' => 'required|string',
                'comprimento' => 'required|numeric|min:0|max:999.99',
                'largura' => 'required|numeric|min:0|max:999.99',
                'calado' => 'required|numeric|min:0|max:99.99',
                'arqueacao_bruta' => 'required|numeric|min:0|max:999999.99',
                'arqueacao_liquida' => 'required|numeric|min:0|max:999999.99',
                'armador' => 'nullable|string|max:255',
                'agente_maritimo' => 'nullable|string|max:255',
                'capitao' => 'nullable|string|max:255',
                'observacoes' => 'nullable|string|max:1000',
                'status' => 'nullable|string',
                'is_active' => 'boolean'
            ], [
               
                'nome.required' => 'O nome da embarcação é obrigatório.',
                'imo.required' => 'O número IMO é obrigatório.',
                'imo.unique' => 'Este número IMO já está cadastrado.',
                'mmsi.unique' => 'Este número MMSI já está cadastrado.',
                'bandeira.required' => 'A bandeira é obrigatória.',
                'tipo_embarcacao.required' => 'O tipo de embarcação é obrigatório.',
                'tipo_embarcacao.in' => 'Tipo de embarcação inválido.',
                'comprimento.required' => 'O comprimento é obrigatório.',
                'largura.required' => 'A largura é obrigatória.',
                'calado.required' => 'O calado é obrigatório.',
                'arqueacao_bruta.required' => 'A arqueação bruta é obrigatória.',
                'arqueacao_liquida.required' => 'A arqueação líquida é obrigatória.'
            ]);

            DB::beginTransaction();
Log::info('EmbarcacaoController@store: salvar de embarcações');
            $embarcacao = Embarcacao::create($validated);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'create',
                'auditable_type' => 'App\\Models\\Embarcacao',
                'auditable_id' => $embarcacao->id,
                'description' => "Criou embarcação: {$embarcacao->nome} (IMO: {$embarcacao->imo})",
                'new_values' => $validated,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            return redirect()->route('embarcacoes.index')
                ->with('success', 'Embarcação cadastrada com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao cadastrar embarcação: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified embarcacao
     */
    public function show(Embarcacao $embarcacao)
{
    try {
        $embarcacao->load(['entradasSaidas' => function ($query) {
            $query->with(['usuario'])->latest()->take(10);
        }]);

        // Log da ação
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'view',
            'auditable_type' => 'App\\Models\\Embarcacao',
            'auditable_id' => $embarcacao->id,
            'description' => "Visualizou embarcação: {$embarcacao->nome}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return view('embarcacoes.show', compact('embarcacao'));
    } catch (\Exception $e) {
        return back()->with('error', 'Erro ao carregar embarcação: ' . $e->getMessage());
    }
}

    /**
     * Show the form for editing the specified embarcacao
     */
    public function edit(Embarcacao $embarcacao)
    {
        try {
            return view('embarcacoes.edit', compact('embarcacao'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar formulário: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified embarcacao
     */
    public function update(Request $request, Embarcacao $embarcacao)
    {
        try {
            $validated = $request->validate([
              
                'nome' => 'required|string|max:255',
                'imo' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('embarcacoes', 'imo')->ignore($embarcacao->id)
                ],
                'mmsi' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('embarcacoes', 'mmsi')->ignore($embarcacao->id)
                ],
                'bandeira' => 'required|string|max:100',
                'tipo_embarcacao' => 'required|string',
                'comprimento' => 'required|numeric|min:0|max:999.99',
                'largura' => 'required|numeric|min:0|max:999.99',
                'calado' => 'required|numeric|min:0|max:99.99',
                'arqueacao_bruta' => 'required|numeric|min:0|max:999999.99',
                'arqueacao_liquida' => 'required|numeric|min:0|max:999999.99',
                'armador' => 'nullable|string|max:255',
                'agente_maritimo' => 'nullable|string|max:255',
                'capitao' => 'nullable|string|max:255',
                'observacoes' => 'nullable|string|max:1000',
                'status' => 'nullable|string',
                'is_active' => 'boolean'
            ], [
               
                'nome.required' => 'O nome da embarcação é obrigatório.',
                'imo.required' => 'O número IMO é obrigatório.',
                'imo.unique' => 'Este número IMO já está cadastrado.',
                'mmsi.unique' => 'Este número MMSI já está cadastrado.',
                'bandeira.required' => 'A bandeira é obrigatória.',
                'tipo_embarcacao.required' => 'O tipo de embarcação é obrigatório.',
                'tipo_embarcacao.in' => 'Tipo de embarcação inválido.'
            ]);

            DB::beginTransaction();

            $originalData = $embarcacao->toArray();
            $embarcacao->update($validated);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'App\\Models\\Embarcacao',
                'auditable_id' => $embarcacao->id,
                'description' => "Atualizou embarcação: {$embarcacao->nome}",
                'new_values' => $validated,
                'changes' => json_encode([
                    'before' => $originalData,
                    'after' => $validated
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route('embarcacoes.index')
                ->with('success', 'Embarcação atualizada com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar embarcação: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified embarcacao
     */
    public function destroy(Embarcacao $embarcacao)
    {
        try {
            DB::beginTransaction();

            $nome = $embarcacao->nome;
            
            // Excluir todos os movimentos associados primeiro
            if ($embarcacao->entradasSaidas()->exists()) {
                $movimentos = $embarcacao->entradasSaidas()->get();
                
                // Log dos movimentos que serão excluídos
                foreach ($movimentos as $movimento) {
                    AuditLog::create([
                        'user_id' => Auth::id(),
                        'event' => 'delete',
                        'auditable_type' => 'App\\Models\\EntradaSaidaEmbarcacao',
                        'auditable_id' => $movimento->id,
                        'description' => "Excluiu movimento da embarcação {$nome} (ID: {$movimento->id}) - exclusão em cascata",
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent()
                    ]);
                }
                
                // Excluir os movimentos
                $embarcacao->entradasSaidas()->delete();
            }

            // Agora excluir a embarcação
            $embarcacao->delete();

            // Log da ação principal
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'delete',
                'auditable_type' => 'App\\Models\\Embarcacao',
                'auditable_id' => $embarcacao->id,
                'description' => "Excluiu embarcação: {$nome}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return redirect()->route('embarcacoes.index')
                ->with('success', 'Embarcação e todos os movimentos associados foram excluídos com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao excluir embarcação: ' . $e->getMessage());
        }
    }

    /**
     * Export embarcacoes to Excel
     */
    public function export(Request $request)
    {
        try {
            // Implementar exportação para Excel
            // Usar Laravel Excel ou similar
            
            return back()->with('info', 'Funcionalidade de exportação será implementada em breve.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao exportar dados: ' . $e->getMessage());
        }
    }

    /**
     * Show import form
     */
    public function import()
    {
        try {
            return view('embarcacoes.import');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao carregar formulário: ' . $e->getMessage());
        }
    }

    /**
     * Process import file
     */
    public function processImport(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
            ], [
                'file.required' => 'Selecione um arquivo para importar.',
                'file.mimes' => 'O arquivo deve ser do tipo Excel ou CSV.',
                'file.max' => 'O arquivo não pode ser maior que 10MB.'
            ]);

            // Implementar importação de Excel/CSV
            // Usar Laravel Excel ou similar
            
            return back()->with('info', 'Funcionalidade de importação será implementada em breve.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao processar importação: ' . $e->getMessage());
        }
    }

    /**
     * Toggle embarcacao status
     */
    public function toggleStatus(Embarcacao $embarcacao)
    {
        try {
            DB::beginTransaction();

            $embarcacao->update(['is_active' => !$embarcacao->is_active]);
            $status = $embarcacao->is_active ? 'ativada' : 'desativada';

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'model_type' => 'Embarcacao',
                'model_id' => $embarcacao->id,
                'description' => "Embarcação {$embarcacao->nome} foi {$status}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            DB::commit();

            return back()->with('success', "Embarcação {$status} com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao alterar status: ' . $e->getMessage());
        }
    }

    /**
     * Get embarcacoes for API/AJAX
     */
    public function api(Request $request)
    {
        try {
            $embarcacoes = Embarcacao::query()
                ->when($request->search, function ($q) use ($request) {
                    $q->where('nome', 'like', '%' . $request->search . '%')
                        ->orWhere('imo', 'like', '%' . $request->search . '%');
                })
                ->ativas()
                ->orderBy('nome')
                ->limit(50)
                ->get()
                ->map(function ($embarcacao) {
                    return [
                        'id' => $embarcacao->id,
                        'nome' => $embarcacao->nome,
                        'imo' => $embarcacao->imo,
                        'mmsi' => $embarcacao->mmsi,
                        'bandeira' => $embarcacao->bandeira,
                        'tipo' => $embarcacao->tipo_embarcacao
                    ];
                });

            return response()->json($embarcacoes);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar embarcações'], 500);
        }
    }

    /**
     * Generate report with filtered embarcacoes (PDF or Excel)
     */
    public function relatorioFiltrado(Request $request)
    {
        try {
            $formato = $request->get('formato', 'pdf'); // Default para PDF
            
            // Aplicar os mesmos filtros do método index
            $query = Embarcacao::query()
                ->when($request->search, function ($q) use ($request) {
                    $q->where(function ($query) use ($request) {
                        $query->where('nome', 'like', '%' . $request->search . '%')
                            ->orWhere('imo', 'like', '%' . $request->search . '%')
                            ->orWhere('mmsi', 'like', '%' . $request->search . '%')
                            ->orWhere('bandeira', 'like', '%' . $request->search . '%');
                    });
                })
                ->when($request->status, function ($q) use ($request) {
                    $q->where('status', $request->status);
                })
                ->when($request->tipo_embarcacao, function ($q) use ($request) {
                    $q->where('tipo_embarcacao', $request->tipo_embarcacao);
                })
                ->when($request->bandeira, function ($q) use ($request) {
                    $q->where('bandeira', $request->bandeira);
                })
                ->when($request->data_inicio && $request->data_fim, function ($q) use ($request) {
                    $q->whereBetween('created_at', [
                        Carbon::parse($request->data_inicio)->startOfDay(),
                        Carbon::parse($request->data_fim)->endOfDay()
                    ]);
                });

            $embarcacoes = $query->orderBy('nome', 'asc')->get();

            // Preparar dados dos filtros aplicados
            $filtrosAplicados = [];
            if ($request->search) $filtrosAplicados['Busca'] = $request->search;
            if ($request->status) $filtrosAplicados['Status'] = $request->status;
            if ($request->tipo_embarcacao) $filtrosAplicados['Tipo'] = $request->tipo_embarcacao;
            if ($request->bandeira) $filtrosAplicados['Bandeira'] = $request->bandeira;
            if ($request->data_inicio && $request->data_fim) {
                $filtrosAplicados['Período'] = Carbon::parse($request->data_inicio)->format('d/m/Y') . ' a ' . Carbon::parse($request->data_fim)->format('d/m/Y');
            }

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'export_report',
                'auditable_type' => 'App\\Models\\Embarcacao',
                'auditable_id' => null,
                'description' => "Gerou relatório de embarcações em formato {$formato} com " . $embarcacoes->count() . " registros",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Gerar relatório baseado no formato
            if ($formato === 'excel') {
                return $this->generateExcelReport($embarcacoes, $filtrosAplicados);
            } else {
                return $this->generatePdfReport($embarcacoes, $filtrosAplicados);
            }

        } catch (\Exception $e) {
            Log::error('EmbarcacaoController@relatorioFiltrado: Erro ao gerar relatório', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'filters' => $request->all(),
                'formato' => $formato ?? 'pdf'
            ]);
            
            return back()->with('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF report
     */
    private function generatePdfReport($embarcacoes, $filtrosAplicados)
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
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('embarcacoes.relatorio-pdf', compact('embarcacoes', 'configuracoes', 'filtrosAplicados'));
        
        // Configurações do PDF
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'Arial',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
            'dpi' => 150
        ]);

        $filename = 'relatorio_embarcacoes_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Generate Excel report
     */
    private function generateExcelReport($embarcacoes, $filtrosAplicados)
    {
        $filename = 'relatorio_embarcacoes_' . now()->format('Y-m-d_H-i-s') . '.xls';
        
        // Gerar conteúdo CSV compatível com Excel
        $csvContent = $this->generateExcelCsv($embarcacoes, $filtrosAplicados);
        
        // Headers para download compatível com Office 2013
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
            'Pragma' => 'public',
            'Expires' => '0'
        ];
        
        return response($csvContent, 200, $headers);
    }

    /**
     * Generate CSV content compatible with Excel
     */
    private function generateExcelCsv($embarcacoes, $filtrosAplicados)
    {
        // BOM para UTF-8 (necessário para caracteres especiais no Excel)
        $csv = "\xEF\xBB\xBF";
        
        // Cabeçalho do relatório
        $csv .= "PORTO DE SOYO\n";
        $csv .= "Sistema Integrado de Gestão Portuária\n";
        $csv .= "Relatório de Embarcações\n\n";
        
        // Informações do relatório
        $csv .= "Data de Geração:;" . now()->format('d/m/Y H:i:s') . "\n";
        $csv .= "Total de Embarcações:;" . $embarcacoes->count() . "\n\n";
        
        // Filtros aplicados
        if (!empty($filtrosAplicados)) {
            $csv .= "Filtros Aplicados:\n";
            foreach ($filtrosAplicados as $filtro => $valor) {
                $csv .= $filtro . ":;" . $valor . "\n";
            }
            $csv .= "\n";
        }
        
        // Cabeçalhos da tabela
        $csv .= "Nome;IMO;MMSI;Bandeira;Tipo;Comprimento (m);Largura (m);Calado (m);Arqueação Bruta;Status;Data Cadastro\n";
        
        // Dados das embarcações
        foreach ($embarcacoes as $embarcacao) {
            $csv .= sprintf(
                '"%s";"%s";"%s";"%s";"%s";%s;%s;%s;%s;"%s";"%s"' . "\n",
                str_replace('"', '""', $embarcacao->nome ?? ''),
                str_replace('"', '""', $embarcacao->imo ?? ''),
                str_replace('"', '""', $embarcacao->mmsi ?? ''),
                str_replace('"', '""', $embarcacao->bandeira ?? ''),
                str_replace('"', '""', $embarcacao->tipo_embarcacao ?? ''),
                number_format($embarcacao->comprimento ?? 0, 2, ',', ''),
                number_format($embarcacao->largura ?? 0, 2, ',', ''),
                number_format($embarcacao->calado ?? 0, 2, ',', ''),
                number_format($embarcacao->arqueacao_bruta ?? 0, 0, ',', ''),
                str_replace('"', '""', ucfirst($embarcacao->status ?? '')),
                $embarcacao->created_at ? $embarcacao->created_at->format('d/m/Y H:i:s') : ''
            );
        }
        
        $csv .= "\n";
        $csv .= "Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária\n";
        
        return $csv;
    }

    /**
     * Generate HTML content for Excel export (método antigo - manter para compatibilidade)
     */
    private function generateExcelHtml($embarcacoes, $filtrosAplicados)
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Relatório de Embarcações</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h1 { color: #2563eb; margin: 0; }
                .header h2 { color: #64748b; margin: 5px 0; }
                .info { margin-bottom: 15px; }
                .filters { background-color: #f8fafc; padding: 10px; margin-bottom: 15px; border: 1px solid #e2e8f0; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
                th { background-color: #f3f4f6; font-weight: bold; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .status-ativo { background-color: #dcfce7; color: #166534; }
                .status-inativo { background-color: #fef2f2; color: #991b1b; }
                .status-manutencao { background-color: #fef3c7; color: #92400e; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>PORTO DE SOYO</h1>
                <h2>Sistema Integrado de Gestão Portuária</h2>
                <h3>Relatório de Embarcações</h3>
            </div>
            
            <div class="info">
                <p><strong>Data de Geração:</strong> ' . now()->format('d/m/Y H:i:s') . '</p>
                <p><strong>Total de Embarcações:</strong> ' . $embarcacoes->count() . '</p>
            </div>';

        if (!empty($filtrosAplicados)) {
            $html .= '<div class="filters">
                <h4>Filtros Aplicados:</h4>
                <ul>';
            foreach ($filtrosAplicados as $filtro => $valor) {
                $html .= '<li><strong>' . $filtro . ':</strong> ' . $valor . '</li>';
            }
            $html .= '</ul></div>';
        }

        $html .= '<table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>IMO</th>
                    <th>MMSI</th>
                    <th>Bandeira</th>
                    <th>Tipo</th>
                    <th>Comprimento (m)</th>
                    <th>Largura (m)</th>
                    <th>Calado (m)</th>
                    <th>Arqueação Bruta</th>
                    <th>Status</th>
                    <th>Data Cadastro</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($embarcacoes as $embarcacao) {
            $statusClass = '';
            switch ($embarcacao->status) {
                case 'ativo':
                    $statusClass = 'status-ativo';
                    break;
                case 'inativo':
                    $statusClass = 'status-inativo';
                    break;
                case 'manutencao':
                    $statusClass = 'status-manutencao';
                    break;
            }

            $html .= '<tr>
                <td>' . htmlspecialchars($embarcacao->nome) . '</td>
                <td>' . htmlspecialchars($embarcacao->imo) . '</td>
                <td>' . htmlspecialchars($embarcacao->mmsi) . '</td>
                <td>' . htmlspecialchars($embarcacao->bandeira) . '</td>
                <td>' . htmlspecialchars($embarcacao->tipo_embarcacao) . '</td>
                <td class="text-right">' . number_format($embarcacao->comprimento, 2, ',', '.') . '</td>
                <td class="text-right">' . number_format($embarcacao->largura, 2, ',', '.') . '</td>
                <td class="text-right">' . number_format($embarcacao->calado, 2, ',', '.') . '</td>
                <td class="text-right">' . number_format($embarcacao->arqueacao_bruta, 0, ',', '.') . '</td>
                <td class="' . $statusClass . '">' . ucfirst($embarcacao->status) . '</td>
                <td class="text-center">' . $embarcacao->created_at->format('d/m/Y H:i:s') . '</td>
            </tr>';
        }

        $html .= '</tbody>
        </table>
        
        <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #64748b;">
            <p>Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária</p>
        </div>
        
        </body>
        </html>';

        return $html;
    }

    /**
     * Exporta uma embarcação específica para PDF
     */
    public function exportPdf(Embarcacao $embarcacao)
    {
        try {
            // Carregar relacionamentos necessários
            $embarcacao->load(['entradasSaidas' => function ($query) {
                $query->with(['usuario', 'terminal'])->latest()->take(10);
            }]);

            // Obter configurações do sistema
            $configuracoes = [
                'logotipo' => \App\Helpers\ConfigHelper::get('logotipo_sistema', ''),
                'cabecalho' => \App\Helpers\ConfigHelper::get('cabecalho_documentos', [
                    'titulo' => 'PORTO DE SOYO',
                    'subtitulo' => 'Sistema Integrado de Gestão Portuária',
                    'endereco' => 'Soyo, Província do Zaire, Angola',
                    'telefone' => '+244 xxx xxx xxx',
                    'email' => 'contato@portodesoyo.ao',
                    'website' => 'www.portodesoyo.ao'
                ]),
                'rodape' => \App\Helpers\ConfigHelper::get('rodape_relatorios', 'Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária')
            ];

            // Se cabecalho_documentos for string JSON, decodificar
            if (is_string($configuracoes['cabecalho'])) {
                $configuracoes['cabecalho'] = json_decode($configuracoes['cabecalho'], true) ?: $configuracoes['cabecalho'];
            }

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'export_pdf',
                'auditable_type' => 'App\\Models\\Embarcacao',
                'auditable_id' => $embarcacao->id,
                'description' => "Exportou PDF da embarcação: {$embarcacao->nome}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Gerar PDF usando DomPDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('embarcacoes.pdf', compact('embarcacao', 'configuracoes'));
            
            // Configurações do PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'Arial',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'dpi' => 150
            ]);

            $filename = 'embarcacao_' . $embarcacao->imo . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('EmbarcacaoController@exportPdf: Erro ao gerar PDF', [
                'error' => $e->getMessage(),
                'embarcacao_id' => $embarcacao->id,
                'user_id' => Auth::id()
            ]);
            
            return back()->with('error', 'Erro ao gerar PDF: ' . $e->getMessage());
        }
    }
}