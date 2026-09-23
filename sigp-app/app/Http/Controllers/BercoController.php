<?php

namespace App\Http\Controllers;

use App\Models\Berco;
use App\Models\Terminal;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class BercoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Berco::query()->with('terminal')
            ->when($request->search, function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->search . '%');
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->terminal_id, function ($q) use ($request) {
                $q->where('terminal_id', $request->terminal_id);
            });

        $bercos = $query->orderBy($request->get('sort', 'nome'), $request->get('direction', 'asc'))
            ->paginate($request->get('per_page', 15))
            ->withQueryString();

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'view',
            'auditable_type' => 'Berco',
            'description' => 'Visualizou listagem de berços',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'created_at' => now()
        ]);

        $terminais = Terminal::orderBy('nome')->get();
        return view('bercos.index', compact('bercos', 'terminais'));
    }

    public function create(Request $request)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'view',
            'auditable_type' => 'Berco',
            'description' => 'Visualizou formulário de criação de berço',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'created_at' => now()
        ]);

        $terminais = Terminal::where('is_active', true)->orderBy('nome')->get();
        $bercos = Berco::with('terminal')->orderBy('nome')->paginate(10);
        return view('bercos.create', compact('terminais', 'bercos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'terminal_id' => 'required|exists:terminais,id',
            'nome' => [
                'required','string','max:255',
                Rule::unique('bercos', 'nome')->where(fn($q) => $q->where('terminal_id', $request->terminal_id))
            ],
            'calado_maximo' => 'nullable|numeric|min:0',
            'comprimento_maximo' => 'nullable|numeric|min:0',
            'status' => 'required|in:disponivel,ocupado,manutencao',
        ]);

        DB::beginTransaction();
        try {
            $berco = Berco::create($validated);

            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'create',
                'auditable_type' => 'Berco',
                'auditable_id' => $berco->id,
                'description' => 'Criou berço: ' . $berco->nome,
                'new_values' => $berco->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'created_at' => now()
            ]);

            DB::commit();
            return redirect()->route('bercos.index')->with('success', 'Berço criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar berço: ' . $e->getMessage());
            return back()->with('error', 'Erro ao criar berço.')->withInput();
        }
    }

    public function show(Berco $berco)
    {
        $berco->load('terminal');
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'view',
            'auditable_type' => 'Berco',
            'auditable_id' => $berco->id,
            'description' => 'Visualizou berço: ' . $berco->nome,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'created_at' => now()
        ]);
        return view('bercos.show', compact('berco'));
    }

    public function edit(Berco $berco)
    {
        $terminais = Terminal::where('is_active', true)->orderBy('nome')->get();
        return view('bercos.edit', compact('berco', 'terminais'));
    }

    public function update(Request $request, Berco $berco)
    {
        $validated = $request->validate([
            'terminal_id' => 'required|exists:terminais,id',
            'nome' => [
                'required','string','max:255',
                Rule::unique('bercos', 'nome')->ignore($berco->id, 'id')->where(fn($q) => $q->where('terminal_id', $request->terminal_id))
            ],
            'calado_maximo' => 'nullable|numeric|min:0',
            'comprimento_maximo' => 'nullable|numeric|min:0',
            'status' => 'required|in:disponivel,ocupado,manutencao',
        ]);

        DB::beginTransaction();
        try {
            $old = $berco->getOriginal();
            $berco->update($validated);

            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'Berco',
                'auditable_id' => $berco->id,
                'description' => 'Atualizou berço: ' . $berco->nome,
                'old_values' => $old,
                'new_values' => $berco->getChanges(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'created_at' => now()
            ]);

            DB::commit();
            return redirect()->route('bercos.show', $berco)->with('success', 'Berço atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar berço: ' . $e->getMessage());
            return back()->with('error', 'Erro ao atualizar berço.')->withInput();
        }
    }

    public function destroy(Berco $berco)
    {
        DB::beginTransaction();
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'delete',
                'auditable_type' => 'Berco',
                'auditable_id' => $berco->id,
                'description' => 'Excluiu berço: ' . $berco->nome,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'created_at' => now()
            ]);

            $berco->delete();
            DB::commit();
            return redirect()->route('bercos.index')->with('success', 'Berço excluído com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir berço: ' . $e->getMessage());
            return back()->with('error', 'Erro ao excluir berço.');
        }
    }
}
