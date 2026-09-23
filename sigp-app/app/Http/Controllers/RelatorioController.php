<?php

namespace App\Http\Controllers;

use App\Models\Incidente;
use App\Models\Inspecao;
use App\Models\InfracaoAmbiental;
use App\Models\Embarcacao;
use App\Models\Terminal;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RelatorioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('permission:relatorios.view');
    }

    /**
     * Display a listing of available reports.
     */
    public function index()
    {
        $relatoriosDisponiveis = [
            'geral' => [
                'titulo' => 'Relatório Geral',
                'descricao' => 'Visão geral de todas as operações do sistema',
                'icone' => 'chart-bar'
            ],
            'incidentes' => [
                'titulo' => 'Relatório de Incidentes',
                'descricao' => 'Análise detalhada dos incidentes registrados',
                'icone' => 'exclamation-triangle'
            ],
            'inspecoes' => [
                'titulo' => 'Relatório de Inspeções',
                'descricao' => 'Histórico e resultados das inspeções realizadas',
                'icone' => 'search'
            ],
            'infracoes' => [
                'titulo' => 'Relatório de Infrações Ambientais',
                'descricao' => 'Registro de infrações ambientais e penalidades',
                'icone' => 'leaf'
            ],
            'embarcacoes' => [
                'titulo' => 'Relatório de Embarcações',
                'descricao' => 'Status e movimentação das embarcações',
                'icone' => 'ship'
            ],
            'terminais' => [
                'titulo' => 'Relatório de Terminais',
                'descricao' => 'Operações e estatísticas dos terminais',
                'icone' => 'building'
            ]
        ];

        return view('relatorios.index', compact('relatoriosDisponiveis'));
    }

    /**
     * Generate general report
     */
    public function geral(Request $request)
    {
        $dataInicio = $request->get('data_inicio', now()->subMonth()->format('Y-m-d'));
        $dataFim = $request->get('data_fim', now()->format('Y-m-d'));

        $dados = [
            'periodo' => ['inicio' => $dataInicio, 'fim' => $dataFim],
            'incidentes' => Incidente::whereBetween('data_ocorrencia', [$dataInicio, $dataFim])->count(),
            'inspecoes' => Inspecao::whereBetween('data_inspecao', [$dataInicio, $dataFim])->count(),
            'infracoes' => InfracaoAmbiental::whereBetween('data_infracao', [$dataInicio, $dataFim])->count(),
            'embarcacoes_ativas' => Embarcacao::whereIn('status', ['atracado', 'operando'])->count(),
            'terminais_operacionais' => Terminal::where('status', 'ativo')->count()
        ];

        return view('relatorios.geral', compact('dados'));
    }

    /**
     * Generate incidents report
     */
    public function incidentes(Request $request)
    {
        return redirect()->route('incidentes.relatorio', $request->all());
    }

    /**
     * Generate inspections report
     */
    public function inspecoes(Request $request)
    {
        return redirect()->route('inspecoes.relatorio', $request->all());
    }

    /**
     * Generate environmental violations report
     */
    public function infracoes(Request $request)
    {
        return redirect()->route('infracoes-ambientais.relatorio', $request->all());
    }

    /**
     * Generate vessels report
     */
    public function embarcacoes(Request $request)
    {
        $dataInicio = $request->get('data_inicio', now()->subMonth()->format('Y-m-d'));
        $dataFim = $request->get('data_fim', now()->format('Y-m-d'));

        $embarcacoes = Embarcacao::with(['concessionaria', 'terminal'])
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->get();

        $estatisticas = [
            'total' => $embarcacoes->count(),
            'por_status' => $embarcacoes->groupBy('status')->map->count(),
            'por_tipo' => $embarcacoes->groupBy('tipo')->map->count()
        ];

        return view('relatorios.embarcacoes', compact('embarcacoes', 'estatisticas'));
    }

    /**
     * Generate terminals report
     */
    public function terminais(Request $request)
    {
        $terminais = Terminal::with(['embarcacoes'])->get();

        $estatisticas = [
            'total' => $terminais->count(),
            'ativos' => $terminais->where('status', 'ativo')->count(),
            'inativos' => $terminais->where('status', 'inativo')->count()
        ];

        return view('relatorios.terminais', compact('terminais', 'estatisticas'));
    }

    /**
     * Generate consolidated report
     */
    public function consolidado(Request $request)
    {
        $dataInicio = $request->get('data_inicio', now()->subMonth()->format('Y-m-d'));
        $dataFim = $request->get('data_fim', now()->format('Y-m-d'));
        $formato = $request->get('formato', 'pdf');

        // Coletar dados de todos os módulos
        $incidentes = Incidente::whereBetween('created_at', [$dataInicio, $dataFim])->get();
        $inspecoes = Inspecao::whereBetween('created_at', [$dataInicio, $dataFim])->get();
        $infracoes = InfracaoAmbiental::whereBetween('created_at', [$dataInicio, $dataFim])->get();
        $embarcacoes = Embarcacao::whereBetween('created_at', [$dataInicio, $dataFim])->get();
        $terminais = Terminal::all();

        $dados = [
            'periodo' => [
                'inicio' => $dataInicio,
                'fim' => $dataFim
            ],
            'incidentes' => [
                'total' => $incidentes->count(),
                'criticos' => $incidentes->where('prioridade', 'crítica')->count(),
                'em_investigacao' => $incidentes->where('status', 'em investigação')->count(),
                'dados' => $incidentes
            ],
            'inspecoes' => [
                'total' => $inspecoes->count(),
                'concluidas' => $inspecoes->where('status', 'concluída')->count(),
                'em_andamento' => $inspecoes->where('status', 'em andamento')->count(),
                'dados' => $inspecoes
            ],
            'infracoes' => [
                'total' => $infracoes->count(),
                'resolvidas' => $infracoes->where('status', 'resolvida')->count(),
                'pendentes' => $infracoes->where('status', 'pendente')->count(),
                'dados' => $infracoes
            ],
            'embarcacoes' => [
                'total' => $embarcacoes->count(),
                'dados' => $embarcacoes
            ],
            'terminais' => [
                'total' => $terminais->count(),
                'ativos' => $terminais->where('status', 'ativo')->count(),
                'dados' => $terminais
            ]
        ];

        // Se for uma requisição de exportação, retornar dados para download
        if ($request->has('export') || $formato !== 'html') {
            return $this->exportConsolidado($dados, $formato);
        }

        // Caso contrário, retornar view
        return view('relatorios.consolidado', compact('dados'));
    }

    /**
     * Export consolidated report in different formats
     */
    private function exportConsolidado($dados, $formato)
    {
        switch ($formato) {
            case 'pdf':
                // Implementar exportação PDF
                return response()->json(['message' => 'Exportação PDF em desenvolvimento', 'dados' => $dados]);
            case 'excel':
                // Implementar exportação Excel
                return response()->json(['message' => 'Exportação Excel em desenvolvimento', 'dados' => $dados]);
            case 'csv':
                // Implementar exportação CSV
                return response()->json(['message' => 'Exportação CSV em desenvolvimento', 'dados' => $dados]);
            default:
                return response()->json(['message' => 'Formato não suportado', 'dados' => $dados]);
        }
    }

    /**
     * Export report
     */
    public function export(Request $request)
    {
        // Implementar lógica de exportação
        return response()->json(['message' => 'Funcionalidade de exportação em desenvolvimento']);
    }

    /**
     * Generate custom report
     */
    public function generate(Request $request)
    {
        // Implementar geração de relatórios customizados
        return response()->json(['message' => 'Geração de relatórios customizados em desenvolvimento']);
    }

    /**
     * Generate concessionarias report
     */
    public function concessionarias(Request $request)
    {
        $dataInicio = $request->get('data_inicio', now()->subMonth()->format('Y-m-d'));
        $dataFim = $request->get('data_fim', now()->format('Y-m-d'));

        $concessionarias = \App\Models\Concessionaria::with(['embarcacoes', 'terminais'])
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->get();

        $estatisticas = [
            'total' => $concessionarias->count(),
            'ativas' => $concessionarias->where('status', 'ativa')->count(),
            'inativas' => $concessionarias->where('status', 'inativa')->count(),
            'total_embarcacoes' => $concessionarias->sum(function($c) { return $c->embarcacoes->count(); })
        ];

        return view('relatorios.concessionarias', compact('concessionarias', 'estatisticas'));
    }

    /**
     * Generate entrada/saida report
     */
    public function entradaSaida(Request $request)
    {
        return redirect()->route('entrada-saida-embarcacao-concessionaria.relatorio', $request->all());
    }

    /**
     * Generate alertas report
     */
    public function alertas(Request $request)
    {
        $dataInicio = $request->get('data_inicio', now()->subMonth()->format('Y-m-d'));
        $dataFim = $request->get('data_fim', now()->format('Y-m-d'));

        $alertas = \App\Models\Alerta::whereBetween('created_at', [$dataInicio, $dataFim])->get();

        $estatisticas = [
            'total' => $alertas->count(),
            'criticos' => $alertas->where('nivel', 'crítico')->count(),
            'ativos' => $alertas->where('status', 'ativo')->count(),
            'resolvidos' => $alertas->where('status', 'resolvido')->count()
        ];

        return view('relatorios.alertas', compact('alertas', 'estatisticas'));
    }

    /**
     * Generate inspecao navios report
     */
    public function inspecaoNavios(Request $request)
    {
        return redirect()->route('inspecoes.relatorio', $request->all());
    }

    /**
     * Generate inspecao ambiental report
     */
    public function inspecaoAmbiental(Request $request)
    {
        return redirect()->route('infracoes-ambientais.relatorio', $request->all());
    }

    /**
     * Generate pedidos report
     */
    public function pedidos(Request $request)
    {
        return redirect()->route('pedidos.relatorio', $request->all());
    }
}