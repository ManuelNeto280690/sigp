<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Escala;
use App\Models\Embarcacao;
use App\Models\Terminal;
use App\Models\Fal1DeclaracaoGeral;
use App\Models\Fal2DeclaracaoCarga;
use App\Models\Fal3ProvisoesBordo;
use App\Models\Fal4PertencessTripulacao;
use App\Models\Fal5ListaTripulantes;
use App\Models\Fal6ListaPassageiros;
use App\Models\Fal7MercadoriasPerigosas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class JanelaUnicaController extends Controller
{
   /* public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('role:admin|operador|concessionaria|agente_maritimo');
    }*/

    /**
     * Dashboard da Janela Única - Visão geral das escalas
     */
    public function dashboard(Request $request)
    {
        try {
            // Estatísticas básicas
            $escalasAtivas = Escala::whereIn('status', ['planejada', 'confirmada', 'atracada'])->count();
            $escalasHoje = Escala::whereDate('eta_previsto', today())->count();
            $totalEmbarcacoes = Embarcacao::where('is_active', true)->count();
            
            // FALs pendentes (contando escalas que ainda não têm todos os FALs aprovados)
            $falsPendentes = Escala::where('fals_obrigatorios_aprovados', false)->count();
            
            // Aprovações hoje (escalas que foram aprovadas hoje)
            $aprovacoesHoje = Escala::whereDate('updated_at', today())
                ->where('status', 'confirmada')
                ->count();
            
            // Escalas por status para gráfico
            $escalasPorStatus = Escala::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();
            
            // FALs por tipo (simulado - você pode ajustar conforme sua lógica)
            $falsPorTipo = [
                'FAL 1 - Declaração Geral' => Escala::whereHas('fal1')->count(),
                'FAL 2 - Declaração de Carga' => Escala::whereHas('fal2')->count(),
                'FAL 3 - Provisões de Bordo' => Escala::whereHas('fal3')->count(),
                'FAL 4 - Pertences da Tripulação' => Escala::whereHas('fal4')->count(),
                'FAL 5 - Lista de Tripulantes' => Escala::whereHas('fal5')->count(),
                'FAL 6 - Lista de Passageiros' => Escala::whereHas('fal6')->count(),
                'FAL 7 - Mercadorias Perigosas' => Escala::whereHas('fal7')->count(),
            ];
            
            // Escalas recentes
            $escalasRecentes = Escala::with(['embarcacao'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            // Próximas chegadas
            $proximasChegadas = Escala::with(['embarcacao'])
                ->where('eta_previsto', '>=', now())
                ->orderBy('eta_previsto', 'asc')
                ->limit(5)
                ->get();
            
            $stats = [
                'escalas_ativas' => $escalasAtivas,
                'escalas_hoje' => $escalasHoje,
                'total_embarcacoes' => $totalEmbarcacoes,
                'fals_pendentes' => $falsPendentes,
                'aprovacoes_hoje' => $aprovacoesHoje,
                'escalas_por_status' => $escalasPorStatus,
                'fals_por_tipo' => $falsPorTipo,
                'trend_escalas' => '+12%',
                'trend_fals' => '-5%',
                'trend_aprovacoes' => '+8%',
                'trend_embarcacoes' => '+3%'
            ];
            
            return view('janela-unica.dashboard', compact('stats', 'escalasRecentes', 'proximasChegadas'));

        } catch (\Exception $e) {
            Log::error('Erro no dashboard da Janela Única: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar dashboard.');
        }
    }

    /**
     * Lista todas as escalas
     */
    public function index(Request $request)
    {
        try {
            $query = Escala::with(['embarcacao'])
                ->when($request->search, function ($q, $search) {
                    return $q->where('numero_escala', 'like', "%{$search}%")
                           ->orWhere('descricao', 'like', "%{$search}%")
                           ->orWhereHas('embarcacao', function ($eq) use ($search) {
                               $eq->where('nome', 'like', "%{$search}%")
                                  ->orWhere('imo', 'like', "%{$search}%");
                           });
                })
                ->when($request->status, function ($q, $status) {
                    return $q->where('status', $status);
                })
                ->when($request->tipo_operacao, function ($q, $tipo) {
                    return $q->where('tipo_operacao', $tipo);
                })
                ->when($request->data_inicio && $request->data_fim, function ($q) use ($request) {
                    return $q->whereBetween('eta_previsto', [$request->data_inicio, $request->data_fim]);
                });

            $escalas = $query->orderBy('eta_previsto', 'asc')->paginate(15);
            
            return view('janela-unica.index', compact('escalas'));

        } catch (\Exception $e) {
            Log::error('Erro ao listar escalas: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar escalas.');
        }
    }

    /**
     * Mostra o formulário para criar nova escala
     */
    public function create()
    {
        try {
            $embarcacoes = Embarcacao::where('is_active', true)->orderBy('nome')->get();
            
            return view('janela-unica.create', compact('embarcacoes'));

        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de criação: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Armazena uma nova escala
     */
    public function store(Request $request)
    {
        $request->validate([
            'embarcacao_id' => 'required|uuid|exists:embarcacoes,id',
            'tipo_operacao' => 'required|string',
            'eta_previsto' => 'required|date|after:now',
            'etd_previsto' => 'required|date|after:eta_previsto',
            'berco' => 'nullable|string|max:100',
            'status' => 'required|string',
            'descricao' => 'nullable|string|max:1000',
            'observacoes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Gerar número da escala usando o método estático do modelo
            $numeroEscala = Escala::gerarNumeroEscala();

            $escala = Escala::create([
                'numero_escala' => $numeroEscala,
                'embarcacao_id' => $request->embarcacao_id,
                'tipo_operacao' => $request->tipo_operacao,
                'eta_previsto' => $request->eta_previsto,
                'etd_previsto' => $request->etd_previsto,
                'berco' => $request->berco,
                'descricao' => $request->descricao,
                'observacoes' => $request->observacoes,
                'status' => $request->status,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

            // Criar estruturas vazias dos FALs
            $this->criarFalsVazios($escala);

            DB::commit();

            return redirect()->route('janela-unica.show', $escala)
                           ->with('success', 'Escala criada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar escala: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao criar escala: ' . $e->getMessage());
        }
    }

    /**
     * Mostra os detalhes de uma escala
     */
    public function show(Escala $escala)
    {
        try {
            $escala->load([
                'embarcacao',
                'fal1',
                'fal2',
                'fal3',
                'fal4',
                'fal5',
                'fal6',
                'fal7'
            ]);

            return view('janela-unica.show', compact('escala'));

        } catch (\Exception $e) {
            Log::error('Erro ao mostrar escala: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar escala.');
        }
    }

    /**
     * Mostra o formulário para editar escala
     */
    public function edit(Escala $escala)
    {
        try {
            $embarcacoes = Embarcacao::where('is_active', true)->orderBy('nome')->get();
            
            return view('janela-unica.edit', compact('escala', 'embarcacoes'));

        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição: ' . $e->getMessage());
            return back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Atualiza uma escala
     */
    public function update(Request $request, Escala $escala)
    {
        $request->validate([
            'embarcacao_id' => 'required|uuid|exists:embarcacoes,id',
            'tipo_operacao' => 'required|string',
            'status' => 'required|string',
            'eta_previsto' => 'required|date',
            'etd_previsto' => 'required|date|after:eta_previsto',
            'berco' => 'nullable|string|max:100',
            'descricao' => 'nullable|string|max:1000',
            'observacoes' => 'nullable|string|max:1000'
        ]);

        try {
            $escala->update([
                'embarcacao_id' => $request->embarcacao_id,
                'tipo_operacao' => $request->tipo_operacao,
                'eta_previsto' => $request->eta_previsto,
                'etd_previsto' => $request->etd_previsto,
                'berco' => $request->berco,
                'status' => $request->status,
                'descricao' => $request->descricao,
                'observacoes' => $request->observacoes,
                'updated_by' => Auth::id()
            ]);

            return redirect()->route('janela-unica.show', $escala)
                           ->with('success', 'Escala atualizada com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar escala: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao atualizar escala.');
        }
    }

    /**
     * Confirma uma escala (muda status para confirmada)
     */
    public function confirmar(Pedido $pedido)
    {
        try {
            if ($pedido->status_escala !== 'planejada') {
                return back()->with('error', 'Apenas escalas planejadas podem ser confirmadas.');
            }

            $pedido->update([
                'status_escala' => 'confirmada',
                'status' => 'aprovado'
            ]);

            return back()->with('success', 'Escala confirmada com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao confirmar escala: ' . $e->getMessage());
            return back()->with('error', 'Erro ao confirmar escala.');
        }
    }

    /**
     * Registra atracação (muda status para atracada)
     */
    public function atracar(Request $request, Pedido $pedido)
    {
        $request->validate([
            'ata_real' => 'required|date',
            'berco' => 'required|string|max:100'
        ]);

        try {
            if ($pedido->status_escala !== 'confirmada') {
                return back()->with('error', 'Apenas escalas confirmadas podem ser atracadas.');
            }

            $pedido->update([
                'status_escala' => 'atracada',
                'ata_real' => $request->ata_real,
                'berco' => $request->berco
            ]);

            return back()->with('success', 'Atracação registrada com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao registrar atracação: ' . $e->getMessage());
            return back()->with('error', 'Erro ao registrar atracação.');
        }
    }

    /**
     * Registra desatracação (muda status para desatracada)
     */
    public function desatracar(Request $request, Pedido $pedido)
    {
        $request->validate([
            'atd_real' => 'required|date|after:ata_real'
        ]);

        try {
            if ($pedido->status_escala !== 'atracada') {
                return back()->with('error', 'Apenas escalas atracadas podem ser desatracadas.');
            }

            $pedido->update([
                'status_escala' => 'desatracada',
                'atd_real' => $request->atd_real
            ]);

            return back()->with('success', 'Desatracação registrada com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao registrar desatracação: ' . $e->getMessage());
            return back()->with('error', 'Erro ao registrar desatracação.');
        }
    }

    /**
     * Cancela uma escala
     */
    public function cancelar(Request $request, Pedido $pedido)
    {
        $request->validate([
            'motivo_cancelamento' => 'required|string|max:1000'
        ]);

        try {
            if (in_array($pedido->status_escala, ['atracada', 'desatracada'])) {
                return back()->with('error', 'Não é possível cancelar escalas já atracadas ou desatracadas.');
            }

            $pedido->update([
                'status_escala' => 'cancelada',
                'status' => 'cancelado',
                'motivo_rejeicao' => $request->motivo_cancelamento
            ]);

            return back()->with('success', 'Escala cancelada com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao cancelar escala: ' . $e->getMessage());
            return back()->with('error', 'Erro ao cancelar escala.');
        }
    }

    /**
     * Cria estruturas vazias dos FALs para um novo pedido
     */
    private function criarFalsVazios(Pedido $pedido)
    {
        try {
            // FAL 1 - Declaração Geral
            Fal1DeclaracaoGeral::create([
                'pedido_id' => $pedido->id,
                'nome_embarcacao' => '',
                'imo' => '',
                'mmsi' => '',
                'indicativo_chamada' => '',
                'bandeira' => '',
                'porto_registo' => '',
                'arqueacao_bruta' => 0,
                'arqueacao_liquida' => 0,
                'nome_armador' => '',
                'endereco_armador' => '',
                'porto_procedencia' => '',
                'porto_destino' => '',
                'local_atracacao' => '',
                'proposito_escala' => '',
                'numero_tripulantes' => 0,
                'status' => 'rascunho'
            ]);

            // FAL 2 - Declaração de Carga
            Fal2DeclaracaoCarga::create([
                'pedido_id' => $pedido->id,
                'tipo_operacao' => 'embarque',
                'peso_total_carga' => 0,
                'descricao_geral_carga' => '',
                'status' => 'rascunho'
            ]);

            // FAL 3 - Provisões de Bordo
            Fal3ProvisoesBordo::create([
                'pedido_id' => $pedido->id,
                'status' => 'rascunho'
            ]);

            // FAL 4 - Pertences da Tripulação
            Fal4PertencessTripulacao::create([
                'pedido_id' => $pedido->id,
                'status' => 'rascunho'
            ]);

            // FAL 5 - Lista de Tripulantes
            Fal5ListaTripulantes::create([
                'pedido_id' => $pedido->id,
                'total_tripulantes' => 0,
                'status' => 'rascunho'
            ]);

            // FAL 6 - Lista de Passageiros
            Fal6ListaPassageiros::create([
                'pedido_id' => $pedido->id,
                'total_passageiros' => 0,
                'status' => 'rascunho'
            ]);

            // FAL 7 - Mercadorias Perigosas
            Fal7MercadoriasPerigosas::create([
                'pedido_id' => $pedido->id,
                'status' => 'rascunho'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao criar FALs vazios: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calcula progresso detalhado dos FALs
     */
    public function progressoDetalhado(Pedido $pedido)
    {
        $fals = [
            'fal1' => $pedido->fal1,
            'fal2' => $pedido->fal2,
            'fal3' => $pedido->fal3,
            'fal4' => $pedido->fal4,
            'fal5' => $pedido->fal5,
            'fal6' => $pedido->fal6,
            'fal7' => $pedido->fal7,
        ];
    
        $progresso = [];
        $totalAprovados = 0;
    
        foreach ($fals as $key => $fal) {
            if ($fal) {
                $status = $fal->status;
                $progresso[$key] = [
                    'status' => $status,
                    'aprovado' => $status === 'aprovado',
                    'rejeitado' => $status === 'rejeitado',
                    'pendente' => $status === 'rascunho' || $status === 'submetido'
                ];
                
                if ($status === 'aprovado') {
                    $totalAprovados++;
                }
            } else {
                $progresso[$key] = [
                    'status' => 'rascunho',
                    'aprovado' => false,
                    'rejeitado' => false,
                    'pendente' => true
                ];
            }
        }
    
        $progresso['percentual'] = (int) (($totalAprovados / 7) * 100);
        $progresso['total_aprovados'] = $totalAprovados;
        $progresso['total_fals'] = 7;
    
        return $progresso;
    }

    /**
     * Relatório de escalas
     */
    public function relatorio(Request $request)
    {
        try {
            $query = Pedido::with(['embarcacao', 'terminal'])
                ->when($request->data_inicio, function ($q, $data) {
                    return $q->whereDate('eta_previsto', '>=', $data);
                })
                ->when($request->data_fim, function ($q, $data) {
                    return $q->whereDate('eta_previsto', '<=', $data);
                })
                ->when($request->status_escala, function ($q, $status) {
                    return $q->where('status_escala', $status);
                })
                ->when($request->terminal_id, function ($q, $terminal) {
                    return $q->where('terminal_id', $terminal);
                });

            $escalas = $query->orderBy('eta_previsto', 'asc')->get();
            $terminais = Terminal::where('is_active', true)->get();

            return view('janela-unica.relatorio', compact('escalas', 'terminais'));

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar relatório.');
        }
    }
}