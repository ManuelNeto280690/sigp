<?php
namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Concessionaria;
use App\Models\AuditLog;
use App\Models\ContratoTarifa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContratoController extends Controller
{
    public function index(Request $request)
    {
        $query = Contrato::with('concessionaria')
            ->when($request->search, fn($q,$s) => $q->where('titulo','like',"%{$s}%"))
            ->when($request->status, fn($q,$s) => $q->where('status',$s))
            ->when($request->modo_faturacao, fn($q,$m) => $q->where('modo_faturacao',$m))
            ->when($request->concessionaria_id, fn($q,$c) => $q->where('concessionaria_id',$c))
            ->orderByDesc('created_at');

        $contratos = $query->paginate(15);
        $concessionarias = Concessionaria::where('is_active', true)->get();

        return view('contratos.index', compact('contratos','concessionarias'));
    }

    public function create()
    {
        $concessionarias = Concessionaria::where('is_active', true)->get();
        return view('contratos.create', compact('concessionarias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'concessionaria_id' => 'required|exists:concessionarias,id',
            'titulo' => 'nullable|string|max:255',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'modo_faturacao' => 'required|in:por_operacao,consolidado_por_navio',
            'status' => 'required|in:ativo,inativo,cancelado',
            'observacoes' => 'nullable|string'
        ]);

        return DB::transaction(function() use ($data, $request) {
            $contrato = Contrato::create($data);

            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'create',
                'auditable_type' => 'App\\Models\\Contrato',
                'auditable_id' => $contrato->id,
                'description' => 'Criou contrato',
                'new_values' => $data,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('contratos.index')->with('success','Contrato criado.');
        });
    }

    public function show(Contrato $contrato)
    {
        $contrato->load('concessionaria','tarifas');
        return view('contratos.show', compact('contrato'));
    }

    public function edit(Contrato $contrato)
    {
        $concessionarias = Concessionaria::where('is_active', true)->get();
        return view('contratos.edit', compact('contrato','concessionarias'));
    }

    public function update(Request $request, Contrato $contrato)
    {
        $data = $request->validate([
            'concessionaria_id' => 'required|exists:concessionarias,id',
            'titulo' => 'nullable|string|max:255',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'modo_faturacao' => 'required|in:por_operacao,consolidado_por_navio',
            'status' => 'required|in:ativo,inativo,cancelado',
            'observacoes' => 'nullable|string'
        ]);

        return DB::transaction(function() use ($data, $request, $contrato) {
            $old = $contrato->getAttributes();
            $contrato->update($data);

            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'App\\Models\\Contrato',
                'auditable_id' => $contrato->id,
                'description' => 'Atualizou contrato',
                'old_values' => $old,
                'new_values' => $data,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('contratos.show',$contrato)->with('success','Contrato atualizado.');
        });
    }

    public function destroy(Request $request, Contrato $contrato)
    {
        return DB::transaction(function() use ($request, $contrato) {
            $old = $contrato->getAttributes();
            $contrato->delete();

            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'delete',
                'auditable_type' => 'App\\Models\\Contrato',
                'auditable_id' => $contrato->id,
                'description' => 'Excluiu contrato',
                'old_values' => $old,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('contratos.index')->with('success','Contrato excluído.');
        });
    }

    public function storeTarifa(Request $request, Contrato $contrato)
    {
        $data = $request->validate([
            'tipo' => 'required|in:fixa,variavel,penalidade',
            'descricao' => 'required|string|max:255',
            'evento_disparo' => 'required|string|max:255',
            'valor_fixo' => 'nullable|numeric|min:0',
            'valor_unitario' => 'nullable|numeric|min:0',
            'unidade' => 'nullable|string|max:50',
        ]);
        if ((empty($data['valor_fixo']) || $data['valor_fixo'] == 0) && (empty($data['valor_unitario']) || $data['valor_unitario'] == 0)) {
            return back()->withInput()->with('error','Informe um valor fixo ou unitário.');
        }
        return DB::transaction(function() use ($data, $request, $contrato) {
            $tarifa = $contrato->tarifas()->create($data);
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'create',
                'auditable_type' => 'App\\Models\\ContratoTarifa',
                'auditable_id' => $tarifa->id,
                'description' => 'Adicionou tarifa ao contrato',
                'new_values' => $data,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return redirect()->route('contratos.show',$contrato)->with('success','Tarifa adicionada.');
        });
    }

    public function updateTarifa(Request $request, Contrato $contrato, ContratoTarifa $tarifa)
    {
        $data = $request->validate([
            'tipo' => 'required|in:fixa,variavel,penalidade',
            'descricao' => 'required|string|max:255',
            'evento_disparo' => 'required|string|max:255',
            'valor_fixo' => 'nullable|numeric|min:0',
            'valor_unitario' => 'nullable|numeric|min:0',
            'unidade' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);
        if ((empty($data['valor_fixo']) || $data['valor_fixo'] == 0) && (empty($data['valor_unitario']) || $data['valor_unitario'] == 0)) {
            return back()->withInput()->with('error','Informe um valor fixo ou unitário.');
        }
        return DB::transaction(function() use ($data, $request, $contrato, $tarifa) {
            $old = $tarifa->getAttributes();
            $tarifa->update($data);
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'App\\Models\\ContratoTarifa',
                'auditable_id' => $tarifa->id,
                'description' => 'Atualizou tarifa do contrato',
                'old_values' => $old,
                'new_values' => $data,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return redirect()->route('contratos.show',$contrato)->with('success','Tarifa atualizada.');
        });
    }

    public function destroyTarifa(Request $request, Contrato $contrato, ContratoTarifa $tarifa)
    {
        return DB::transaction(function() use ($request, $contrato, $tarifa) {
            $old = $tarifa->getAttributes();
            $tarifa->delete();
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'delete',
                'auditable_type' => 'App\\Models\\ContratoTarifa',
                'auditable_id' => $tarifa->id,
                'description' => 'Removeu tarifa do contrato',
                'old_values' => $old,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return redirect()->route('contratos.show',$contrato)->with('success','Tarifa removida.');
        });
    }
}