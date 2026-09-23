<?php
namespace App\Http\Controllers;

use App\Models\MovimentoCarga;
use App\Models\EntradaSaidaEmbarcacao;
use App\Models\Terminal;
use App\Models\Berco;
use App\Models\Guindaste;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MovimentoCargaController extends Controller
{
    public function index(Request $request)
    {
        $query = MovimentoCarga::with(['entradaSaida'])
            ->when($request->entrada_saida_id, fn($q,$id) => $q->where('entrada_saida_id',$id))
            ->when($request->terminal_id, fn($q,$id) => $q->where('terminal_id',$id))
            ->orderByDesc('created_at');

        $movimentos = $query->paginate(15);
        return view('movimentos-carga.index', compact('movimentos'));
    }

    public function create()
    {
        $entradaSaidas = EntradaSaidaEmbarcacao::orderByDesc('created_at')->limit(50)->get();
        $terminais = Terminal::where('is_active', true)->get();
        $bercos = Berco::orderBy('nome')->get();
        $guindastes = Guindaste::orderBy('nome')->get();
        return view('movimentos-carga.create', compact('entradaSaidas','terminais','bercos','guindastes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'entrada_saida_id' => 'required|exists:entrada_saida_embarcacoes,id',
            'terminal_id' => 'required|exists:terminais,id',
            'berco_id' => 'required|exists:bercos,id',
            'guindaste_id' => 'required|exists:guindastes,id',
            'tipo_operacao' => 'required|in:descarga,carga,transbordo,movimentacao_interna,pesagem,inspecao',
            'tipo_produto' => 'required|in:Container,Granéis Sólidos,Granéis Líquidos,Carga Geral Solta,Ro-Ro,Animais vivos',
            'unidade' => 'required|in:Tonelada,Contêiner,Contêiner (20),Contêiner (40),Unidade,m³,kg,Hora',
            'quantidade' => 'required|numeric|min:0.0001',
            'inicio' => 'required|date',
            'fim' => 'nullable|date|after_or_equal:inicio',
            'operador_id' => 'nullable|exists:users,id',
            'observacoes' => 'nullable|string'
        ]);

        return DB::transaction(function() use ($data, $request) {
            $mov = MovimentoCarga::create($data);

            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'create',
                'auditable_type' => 'App\\Models\\MovimentoCarga',
                'auditable_id' => $mov->id,
                'description' => 'Criou movimento de carga',
                'new_values' => $data,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Se houver fim e guindaste, e flag ativa, tenta faturar uso de equipamento
            if (!empty($mov->fim) && !empty($mov->guindaste_id)) {
                if ($request->has('aplicar_tarifa_operacao') && $request->boolean('aplicar_tarifa_operacao')) {
                    \App\Services\BillingService::appendGuindasteUsage($mov);
                }
            }

            return redirect()->route('movimentos-carga.show',$mov)->with('success','Movimento de carga criado.');
        });
    }

    public function show(MovimentoCarga $movimentos_carga)
    {
        return view('movimentos-carga.show', ['movimento' => $movimentos_carga]);
    }

    public function edit(MovimentoCarga $movimentos_carga)
    {
        $entradaSaidas = EntradaSaidaEmbarcacao::orderByDesc('created_at')->limit(50)->get();
        $terminais = Terminal::where('is_active', true)->get();
        $bercos = Berco::orderBy('nome')->get();
        $guindastes = Guindaste::orderBy('nome')->get();
        return view('movimentos-carga.edit', ['movimento' => $movimentos_carga, 'entradaSaidas' => $entradaSaidas, 'terminais' => $terminais, 'bercos' => $bercos, 'guindastes' => $guindastes]);
    }

    public function update(Request $request, MovimentoCarga $movimentos_carga)
    {
        $data = $request->validate([
            'entrada_saida_id' => 'required|exists:entrada_saida_embarcacoes,id',
            'terminal_id' => 'required|exists:terminais,id',
            'berco_id' => 'required|exists:bercos,id',
            'guindaste_id' => 'required|exists:guindastes,id',
            'tipo_operacao' => 'required|in:descarga,carga,transbordo,movimentacao_interna,pesagem,inspecao',
            'tipo_produto' => 'required|in:Container,Granéis Sólidos,Granéis Líquidos,Carga Geral Solta,Ro-Ro,Animais vivos',
            'unidade' => 'required|in:Tonelada,Contêiner,Contêiner (20),Contêiner (40),Unidade,m³,kg,Hora',
            'quantidade' => 'required|numeric|min:0.0001',
            'inicio' => 'required|date',
            'fim' => 'nullable|date|after_or_equal:inicio',
            'operador_id' => 'nullable|exists:users,id',
            'observacoes' => 'nullable|string'
        ]);

        return DB::transaction(function() use ($data, $request, $movimentos_carga) {
            $old = $movimentos_carga->getAttributes();
            $movimentos_carga->update($data);

            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'App\\Models\\MovimentoCarga',
                'auditable_id' => $movimentos_carga->id,
                'description' => 'Atualizou movimento de carga',
                'old_values' => $old,
                'new_values' => $data,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if (!empty($movimentos_carga->fim) && !empty($movimentos_carga->guindaste_id)) {
                if ($request->has('aplicar_tarifa_operacao') && $request->boolean('aplicar_tarifa_operacao')) {
                    \App\Services\BillingService::appendGuindasteUsage($movimentos_carga);
                }
            }

            return redirect()->route('movimentos-carga.show',$movimentos_carga)->with('success','Movimento de carga atualizado.');
        });
    }

    public function destroy(Request $request, MovimentoCarga $movimentos_carga)
    {
        return DB::transaction(function() use ($request, $movimentos_carga) {
            $old = $movimentos_carga->getAttributes();
            $movimentos_carga->delete();

            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'delete',
                'auditable_type' => 'App\\Models\\MovimentoCarga',
                'auditable_id' => $movimentos_carga->id,
                'description' => 'Excluiu movimento de carga',
                'old_values' => $old,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('movimentos-carga.index')->with('success','Movimento de carga excluído.');
        });
    }

    public function contextoEntradaSaida(EntradaSaidaEmbarcacao $entradaSaida)
    {
        $terminalId = $entradaSaida->terminal_id;
        $bercos = Berco::where('terminal_id', $terminalId)->orderBy('nome')->get(['id','nome']);
        $guindastes = Guindaste::where('terminal_id', $terminalId)->orderBy('nome')->get(['id','nome']);
        $bercoId = null;
        if (!empty($entradaSaida->berco)) {
            $bercoId = Berco::where('terminal_id', $terminalId)->where('nome', $entradaSaida->berco)->value('id');
        }
        $guindasteId = $entradaSaida->guindaste_id;
        return response()->json([
            'terminal_id' => $terminalId,
            'bercos' => $bercos,
            'guindastes' => $guindastes,
            'berco_id' => $bercoId,
            'guindaste_id' => $guindasteId,
        ]);
    }
}