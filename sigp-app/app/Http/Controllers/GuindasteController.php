<?php

namespace App\Http\Controllers;

use App\Models\Guindaste;
use App\Models\Terminal;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class GuindasteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Guindaste::query()->with('terminal')
            ->when($request->search, fn($q) => $q->where('nome', 'like', '%' . $request->search . '%'))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->terminal_id, fn($q) => $q->where('terminal_id', $request->terminal_id))
            ->when($request->tipo, fn($q) => $q->where('tipo', $request->tipo));

        $guindastes = $query
            ->orderBy($request->get('sort', 'nome'), $request->get('direction', 'asc'))
            ->paginate($request->get('per_page', 15))
            ->withQueryString();

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'view',
            'auditable_type' => 'Guindaste',
            'description' => 'Visualizou listagem de guindastes',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'created_at' => now()
        ]);

        $terminais = Terminal::orderBy('nome')->get();

        return view('guinndastes.index', compact('guindastes', 'terminais'));
    }

    public function create(Request $request)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'view',
            'auditable_type' => 'Guindaste',
            'description' => 'Visualizou formulário de criação de guindaste',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'created_at' => now()
        ]);

        $terminais = Terminal::where('is_active', true)->orderBy('nome')->get();

        return view('guinndastes.create', compact('terminais'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'terminal_id' => 'required|exists:terminais,id',
            'nome' => [
                'required','string','max:255',
                Rule::unique('guindastes', 'nome')->where(fn($q) => $q->where('terminal_id', $request->terminal_id))
            ],
            'tipo' => 'required|in:container,graneis_solidos,graneis_liquidos,carga_geral,passageiros,tanque,ro_ro,frigorifico',
            'status' => 'required|in:disponivel,ocupado,manutencao',
        ]);

        DB::beginTransaction();
        try {
            $guindaste = Guindaste::create($validated);

            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'create',
                'auditable_type' => 'Guindaste',
                'auditable_id' => $guindaste->id,
                'description' => 'Criou guindaste: ' . $guindaste->nome,
                'new_values' => $guindaste->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'created_at' => now()
            ]);

            DB::commit();
            return redirect()->route('guindastes.index')->with('success', 'Guindaste criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar guindaste: ' . $e->getMessage());
            return back()->with('error', 'Erro ao criar guindaste.')->withInput();
        }
    }

    public function show(Guindaste $guindaste)
    {
        $guindaste->load('terminal');

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'view',
            'auditable_type' => 'Guindaste',
            'auditable_id' => $guindaste->id,
            'description' => 'Visualizou guindaste: ' . $guindaste->nome,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'created_at' => now()
        ]);

        return view('guinndastes.show', compact('guindaste'));
    }

    public function edit(Guindaste $guindaste)
    {
        $terminais = Terminal::where('is_active', true)->orderBy('nome')->get();
        return view('guinndastes.edit', compact('guindaste', 'terminais'));
    }

    public function update(Request $request, Guindaste $guindaste)
    {
        $validated = $request->validate([
            'terminal_id' => 'required|exists:terminais,id',
            'nome' => [
                'required','string','max:255',
                Rule::unique('guindastes', 'nome')->ignore($guindaste->id, 'id')->where(fn($q) => $q->where('terminal_id', $request->terminal_id))
            ],
            'tipo' => 'required|in:container,graneis_solidos,graneis_liquidos,carga_geral,passageiros,tanque,ro_ro,frigorifico',
            'status' => 'required|in:disponivel,ocupado,manutencao',
        ]);

        DB::beginTransaction();
        try {
            $old = $guindaste->getOriginal();
            $guindaste->update($validated);

            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'Guindaste',
                'auditable_id' => $guindaste->id,
                'description' => 'Atualizou guindaste: ' . $guindaste->nome,
                'old_values' => $old,
                'new_values' => $guindaste->getChanges(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'created_at' => now()
            ]);

            DB::commit();
            return redirect()->route('guindastes.show', $guindaste)->with('success', 'Guindaste atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar guindaste: ' . $e->getMessage());
            return back()->with('error', 'Erro ao atualizar guindaste.')->withInput();
        }
    }

    public function destroy(Guindaste $guindaste)
    {
        DB::beginTransaction();
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'delete',
                'auditable_type' => 'Guindaste',
                'auditable_id' => $guindaste->id,
                'description' => 'Excluiu guindaste: ' . $guindaste->nome,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'created_at' => now()
            ]);

            $guindaste->delete();
            DB::commit();
            return redirect()->route('guindastes.index')->with('success', 'Guindaste excluído com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir guindaste: ' . $e->getMessage());
            return back()->with('error', 'Erro ao excluir guindaste.');
        }
    }
}