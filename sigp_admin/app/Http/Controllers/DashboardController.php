<?php

namespace App\Http\Controllers;

use App\Models\Embarcacao;
use App\Models\Alerta;
use App\Models\Incidente;
use App\Models\Inspecao;
use App\Models\MovimentoTerminal;
use App\Models\EntradaSaidaEmbarcacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Removido o middleware daqui pois já está sendo aplicado nas rotas
    }

    public function index()
    {
        // RESTRIÇÃO DE ACESSO: Proibir concessionárias e agentes de navio
        $user = Auth::user();
        $userRoles = $user->roles->pluck('name')->toArray();
        
        if (in_array('concessionaria', $userRoles)) {
            return redirect()->route('concessionarias.embarcacoes.index')
                ->with('info', 'Você foi redirecionado para sua área específica.');
        }
        
        if (in_array('agente_navio', $userRoles)) {
            return redirect()->route('pedidos.index')
                ->with('info', 'Você foi redirecionado para sua área específica.');
        }

        try {
            // Debug: Verificar se há dados nas tabelas
            $total_embarcacoes = Embarcacao::count();
            $total_alertas = Alerta::count();
            
            Log::info('Dashboard Debug - Total embarcações: ' . $total_embarcacoes);
            Log::info('Dashboard Debug - Total alertas: ' . $total_alertas);
            
            // Estatísticas principais
            $embarcacoes_ativas = Embarcacao::whereIn('status', ['atracado', 'operando'])->count();
            $alertas_ativos = Alerta::where('status', 'ativo')->count();
            
            Log::info('Dashboard Debug - Embarcações ativas: ' . $embarcacoes_ativas);
            Log::info('Dashboard Debug - Alertas ativos: ' . $alertas_ativos);
            
            // Se não há dados, vamos usar dados de exemplo para teste
            if ($embarcacoes_ativas == 0 && $total_embarcacoes > 0) {
                // Há embarcações mas nenhuma com status correto, vamos contar todas as ativas
                $embarcacoes_ativas = Embarcacao::where('is_active', true)->count();
                Log::info('Dashboard Debug - Usando embarcações is_active: ' . $embarcacoes_ativas);
            }
            
            if ($alertas_ativos == 0 && $total_alertas > 0) {
                // Há alertas mas nenhum com status ativo, vamos contar todos os ativos
                $alertas_ativos = Alerta::where('is_active', true)->count();
                Log::info('Dashboard Debug - Usando alertas is_active: ' . $alertas_ativos);
            }
            
            $incidentes_mes = Incidente::whereMonth('data_ocorrencia', now()->month)
                                      ->whereYear('data_ocorrencia', now()->year)
                                      ->count();
            $inspecoes_realizadas = Inspecao::where('status', 'concluida')
                                           ->whereMonth('data_inspecao', now()->month)
                                           ->whereYear('data_inspecao', now()->year)
                                           ->count();

            // Tendências (comparação com mês anterior)
            $embarcacoes_ativas_tendencia = $this->calculateTrend('embarcacoes');
            $alertas_ativos_tendencia = $this->calculateTrend('alertas');
            $incidentes_mes_tendencia = $this->calculateTrend('incidentes');
            $inspecoes_realizadas_tendencia = $this->calculateTrend('inspecoes');

            // Movimentações recentes
            $movimentacoes_recentes = EntradaSaidaEmbarcacao::with(['embarcacao'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Alertas recentes
            $alertas_recentes = Alerta::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Dados para gráficos
            $charts = [
                'movimentacao' => $this->getMovimentacaoChartData(),
                'embarcacoes_tipo' => $this->getEmbarcacoesTipoChartData(),
                'incidentes_gravidade' => $this->getIncidentesGravidadeChartData(),
                'inspecoes_resultado' => $this->getInspecoesResultadoChartData()
            ];

            Log::info('Dashboard Debug - Dados finais:', [
                'embarcacoes_ativas' => $embarcacoes_ativas,
                'alertas_ativos' => $alertas_ativos,
                'incidentes_mes' => $incidentes_mes,
                'inspecoes_realizadas' => $inspecoes_realizadas
            ]);

            return view('dashboard', compact(
                'embarcacoes_ativas',
                'alertas_ativos', 
                'incidentes_mes',
                'inspecoes_realizadas',
                'embarcacoes_ativas_tendencia',
                'alertas_ativos_tendencia',
                'incidentes_mes_tendencia',
                'inspecoes_realizadas_tendencia',
                'movimentacoes_recentes',
                'alertas_recentes',
                'charts'
            ));

        } catch (\Exception $e) {
            Log::error('Erro no dashboard: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Dados padrão em caso de erro
            return view('dashboard', [
                'embarcacoes_ativas' => 0,
                'alertas_ativos' => 0,
                'incidentes_mes' => 0,
                'inspecoes_realizadas' => 0,
                'embarcacoes_ativas_tendencia' => 0,
                'alertas_ativos_tendencia' => 0,
                'incidentes_mes_tendencia' => 0,
                'inspecoes_realizadas_tendencia' => 0,
                'movimentacoes_recentes' => collect(),
                'alertas_recentes' => collect(),
                'charts' => [
                    'movimentacao' => ['labels' => [], 'data' => []],
                    'embarcacoes_tipo' => ['labels' => [], 'data' => []],
                    'incidentes_gravidade' => ['labels' => [], 'data' => []],
                    'inspecoes_resultado' => ['labels' => [], 'data' => []]
                ]
            ])->with('error', 'Erro ao carregar dados do dashboard. Verifique os logs para mais detalhes.');
        }
    }

    private function calculateTrend($type)
    {
        try {
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $previousMonth = now()->subMonth()->month;
            $previousYear = now()->subMonth()->year;

            switch ($type) {
                case 'embarcacoes':
                    $current = Embarcacao::whereMonth('created_at', $currentMonth)
                                        ->whereYear('created_at', $currentYear)
                                        ->count();
                    $previous = Embarcacao::whereMonth('created_at', $previousMonth)
                                         ->whereYear('created_at', $previousYear)
                                         ->count();
                    break;

                case 'alertas':
                    $current = Alerta::whereMonth('created_at', $currentMonth)
                                    ->whereYear('created_at', $currentYear)
                                    ->count();
                    $previous = Alerta::whereMonth('created_at', $previousMonth)
                                     ->whereYear('created_at', $previousYear)
                                     ->count();
                    break;

                case 'incidentes':
                    $current = Incidente::whereMonth('data_ocorrencia', $currentMonth)
                                       ->whereYear('data_ocorrencia', $currentYear)
                                       ->count();
                    $previous = Incidente::whereMonth('data_ocorrencia', $previousMonth)
                                        ->whereYear('data_ocorrencia', $previousYear)
                                        ->count();
                    break;

                case 'inspecoes':
                    $current = Inspecao::whereMonth('data_inspecao', $currentMonth)
                                      ->whereYear('data_inspecao', $currentYear)
                                      ->count();
                    $previous = Inspecao::whereMonth('data_inspecao', $previousMonth)
                                       ->whereYear('data_inspecao', $previousYear)
                                       ->count();
                    break;

                default:
                    return 0;
            }

            if ($previous == 0) {
                return $current > 0 ? 100.0 : 0.0;
            }

            $percentage = (($current - $previous) / $previous) * 100;
            return round($percentage, 1);
        } catch (\Exception $e) {
            Log::error('Erro ao calcular tendência: ' . $e->getMessage());
            return 0;
        }
    }

    private function getMovimentacaoChartData()
    {
        try {
            $data = EntradaSaidaEmbarcacao::select(
                DB::raw('DATE(created_at) as data'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('data')
            ->orderBy('data')
            ->get();

            return [
                'labels' => $data->pluck('data')->map(function($date) {
                    return Carbon::parse($date)->format('d/m');
                })->toArray(),
                'data' => $data->pluck('total')->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados de movimentação: ' . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    private function getEmbarcacoesTipoChartData()
    {
        try {
            $data = Embarcacao::select('tipo_embarcacao', DB::raw('COUNT(*) as total'))
                ->groupBy('tipo_embarcacao')
                ->get();

            return [
                'labels' => $data->pluck('tipo_embarcacao')->toArray(),
                'data' => $data->pluck('total')->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados de embarcações por tipo: ' . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    private function getIncidentesGravidadeChartData()
    {
        try {
            $data = Incidente::select('gravidade', DB::raw('COUNT(*) as total'))
                ->groupBy('gravidade')
                ->get();

            return [
                'labels' => $data->pluck('gravidade')->toArray(),
                'data' => $data->pluck('total')->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados de incidentes por gravidade: ' . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    private function getInspecoesResultadoChartData()
    {
        try {
            $data = Inspecao::select('resultado', DB::raw('COUNT(*) as total'))
                ->groupBy('resultado')
                ->get();

            return [
                'labels' => $data->pluck('resultado')->toArray(),
                'data' => $data->pluck('total')->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados de inspeções por resultado: ' . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    // Métodos AJAX
    public function getStats(Request $request)
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            // Corrigir a estrutura para corresponder ao que o JavaScript espera
            $stats = [
                'embarcacoes_ativas' => Embarcacao::whereIn('status', ['atracado', 'operando'])->count(),
                'alertas_ativos' => Alerta::where('status', 'ativo')->count(),
                'incidentes_mes' => Incidente::when($startDate && $endDate, function($query) use ($startDate, $endDate) {
                    return $query->whereBetween('data_ocorrencia', [$startDate, $endDate]);
                }, function($query) {
                    return $query->whereMonth('data_ocorrencia', now()->month)
                                 ->whereYear('data_ocorrencia', now()->year);
                })->count(),
                'inspecoes_realizadas' => Inspecao::when($startDate && $endDate, function($query) use ($startDate, $endDate) {
                    return $query->whereBetween('data_inspecao', [$startDate, $endDate]);
                }, function($query) {
                    return $query->where('status', 'concluida')
                                 ->whereMonth('data_inspecao', now()->month)
                                 ->whereYear('data_inspecao', now()->year);
                })->count(),
                'tendencias' => [
                    'embarcacoes' => $this->calculateTrendWithDates('embarcacoes', $startDate, $endDate),
                    'alertas' => $this->calculateTrendWithDates('alertas', $startDate, $endDate),
                    'incidentes' => $this->calculateTrendWithDates('incidentes', $startDate, $endDate),
                    'inspecoes' => $this->calculateTrendWithDates('inspecoes', $startDate, $endDate)
                ]
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas: ' . $e->getMessage());
            return response()->json([
                'embarcacoes_ativas' => 0,
                'alertas_ativos' => 0,
                'incidentes_mes' => 0,
                'inspecoes_realizadas' => 0,
                'tendencias' => [
                    'embarcacoes' => 0,
                    'alertas' => 0,
                    'incidentes' => 0,
                    'inspecoes' => 0
                ]
            ]);
        }
    }

    public function getRecentActivities(Request $request)
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $activities = collect();

            // Movimentações recentes
            $movimentacoes = EntradaSaidaEmbarcacao::with(['embarcacao'])
                ->when($startDate && $endDate, function($query) use ($startDate, $endDate) {
                    return $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($mov) {
                    return [
                        'id' => $mov->id,
                        'tipo' => 'movimentacao',
                        'titulo' => 'Movimento: ' . ($mov->embarcacao->nome ?? 'N/A'),
                        'descricao' => $mov->tipo_movimento,
                        'data' => $mov->created_at->format('d/m/Y H:i')
                    ];
                });

            // Alertas recentes
            $alertas = Alerta::when($startDate && $endDate, function($query) use ($startDate, $endDate) {
                    return $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($alerta) {
                    return [
                        'id' => $alerta->id,
                        'tipo' => 'alerta',
                        'titulo' => $alerta->titulo,
                        'descricao' => $alerta->descricao,
                        'data' => $alerta->created_at->format('d/m/Y H:i')
                    ];
                });

            $activities = $activities->merge($movimentacoes)->merge($alertas)
                ->sortByDesc('data')
                ->take(15)
                ->values();

            return response()->json($activities);
        } catch (\Exception $e) {
            Log::error('Erro ao obter atividades recentes: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    private function calculateTrendWithDates($type, $startDate, $endDate)
    {
        try {
            if (!$startDate || !$endDate) {
                return $this->calculateTrend($type);
            }

            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            $duration = $start->diffInDays($end);
            
            $previousStart = $start->copy()->subDays($duration + 1);
            $previousEnd = $start->copy()->subDay();

            switch ($type) {
                case 'embarcacoes':
                    $current = Embarcacao::whereBetween('created_at', [$startDate, $endDate])->count();
                    $previous = Embarcacao::whereBetween('created_at', [$previousStart, $previousEnd])->count();
                    break;

                case 'alertas':
                    $current = Alerta::whereBetween('created_at', [$startDate, $endDate])->count();
                    $previous = Alerta::whereBetween('created_at', [$previousStart, $previousEnd])->count();
                    break;

                case 'incidentes':
                    $current = Incidente::whereBetween('data_ocorrencia', [$startDate, $endDate])->count();
                    $previous = Incidente::whereBetween('data_ocorrencia', [$previousStart, $previousEnd])->count();
                    break;

                case 'inspecoes':
                    $current = Inspecao::whereBetween('data_inspecao', [$startDate, $endDate])->count();
                    $previous = Inspecao::whereBetween('data_inspecao', [$previousStart, $previousEnd])->count();
                    break;

                default:
                    return 0;
            }

            if ($previous == 0) {
                return $current > 0 ? 100.0 : 0.0;
            }

            $percentage = (($current - $previous) / $previous) * 100;
            return round($percentage, 1);
        } catch (\Exception $e) {
            Log::error('Erro ao calcular tendência: ' . $e->getMessage());
            return 0;
        }
    }

    public function getChartData(Request $request)
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            
            return response()->json([
                'movimentacoes' => $this->getMovimentacaoChartDataWithDates($startDate, $endDate),
                'embarcacoes_tipo' => $this->getEmbarcacoesTipoChartDataWithDates($startDate, $endDate),
                'incidentes_gravidade' => $this->getIncidentesGravidadeChartDataWithDates($startDate, $endDate),
                'inspecoes_resultado' => $this->getInspecoesResultadoChartDataWithDates($startDate, $endDate)
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados dos gráficos: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    private function getMovimentacaoChartDataWithDates($startDate, $endDate)
    {
        try {
            $query = EntradaSaidaEmbarcacao::select(
                DB::raw('DATE(created_at) as data'),
                DB::raw('COUNT(*) as total')
            );

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } else {
                $query->where('created_at', '>=', now()->subDays(30));
            }

            $data = $query->groupBy('data')->orderBy('data')->get();

            return [
                'labels' => $data->pluck('data')->map(function($date) {
                    return Carbon::parse($date)->format('d/m');
                })->toArray(),
                'data' => $data->pluck('total')->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados de movimentação com datas: ' . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    private function getEmbarcacoesTipoChartDataWithDates($startDate, $endDate)
    {
        try {
            $query = Embarcacao::select('tipo_embarcacao', DB::raw('COUNT(*) as total'));

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            $data = $query->groupBy('tipo_embarcacao')->get();

            return [
                'labels' => $data->pluck('tipo_embarcacao')->toArray(),
                'data' => $data->pluck('total')->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados de embarcações por tipo com datas: ' . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    private function getIncidentesGravidadeChartDataWithDates($startDate, $endDate)
    {
        try {
            $query = Incidente::select('gravidade', DB::raw('COUNT(*) as total'));

            if ($startDate && $endDate) {
                $query->whereBetween('data_ocorrencia', [$startDate, $endDate]);
            }

            $data = $query->groupBy('gravidade')->get();

            return [
                'labels' => $data->pluck('gravidade')->toArray(),
                'data' => $data->pluck('total')->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados de incidentes por gravidade com datas: ' . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    private function getInspecoesResultadoChartDataWithDates($startDate, $endDate)
    {
        try {
            $query = Inspecao::select('resultado', DB::raw('COUNT(*) as total'));

            if ($startDate && $endDate) {
                $query->whereBetween('data_inspecao', [$startDate, $endDate]);
            }

            $data = $query->groupBy('resultado')->get();

            return [
                'labels' => $data->pluck('resultado')->toArray(),
                'data' => $data->pluck('total')->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados de inspeções por resultado com datas: ' . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }
}