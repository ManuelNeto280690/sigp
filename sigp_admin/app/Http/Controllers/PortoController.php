<?php

namespace App\Http\Controllers;

use App\Models\Porto;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PortoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:portos.view')->only(['index', 'show']);
        $this->middleware('permission:portos.create')->only(['create', 'store']);
        $this->middleware('permission:portos.edit')->only(['edit', 'update']);
        $this->middleware('permission:portos.delete')->only(['destroy']);
    }

    /**
     * Listagem de portos com filtros
     */
    public function index(Request $request)
    {
        $query = Porto::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $inner->where('nome', 'like', '%' . $request->search . '%')
                        ->orWhere('dominio', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('responsavel', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('worker_status', $request->status);
            });

        $portos = $query->orderBy($request->get('sort', 'nome'), $request->get('direction', 'asc'))
            ->paginate($request->get('per_page', 15))
            ->withQueryString();

        $stats = [
            'total' => Porto::count(),
            'running' => Porto::where('worker_status', 'running')->count(),
            'stopped' => Porto::where('worker_status', 'stopped')->count(),
            'idle' => Porto::where('worker_status', 'idle')->count(),
            'error' => Porto::where('worker_status', 'error')->count(),
        ];

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'view',
            'auditable_type' => 'App\\Models\\Porto',
            'auditable_id' => null,
            'url' => $request->url(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return view('portos.index', compact('portos', 'stats'));
    }

    /**
     * Form de criação
     */
    public function create()
    {
        return view('portos.create');
    }

    /**
     * Persistência de novo porto
     */
    public function store(Request $request)
    {
        Log::info('[Portos] store chamado', [
            'user_id' => Auth::id(),
            'payload' => $request->only([
                'nome','slug','email','telefone','endereco','responsavel',
                'telefone_responsavel','num_funcionarios','dominio','worker_status',
                'db_nome','db_usuario','path'
            ]),
            'ip' => $request->ip(),
            'ua' => $request->userAgent(),
        ]);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:portos,slug',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:50',
            'endereco' => 'nullable|string|max:255',
            'responsavel' => 'nullable|string|max:255',
            'telefone_responsavel' => 'nullable|string|max:50',
            'num_funcionarios' => 'nullable|integer|min:0',
            'dominio' => 'nullable|string|max:255|unique:portos,dominio',
            'worker_status' => 'nullable|string|in:inactive,active',
            'db_nome' => 'nullable|string|max:255',
            'db_usuario' => 'nullable|string|max:255',
            'db_senha' => 'nullable|string|max:255',
            'path' => 'nullable|string|max:255',
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['nome']);
        Log::info('[Portos] slug calculado', ['slug' => $slug]);

        $exitCode = null;
        $artisanOutput = null;

        try {
            $exitCode = Artisan::call('porto:criar', ['nome' => $validated['nome']]);
            $artisanOutput = Artisan::output();
            Log::info('[Portos] comando porto:criar executado', [
                'exit_code' => $exitCode,
                'output' => $artisanOutput,
            ]);
        } catch (\Throwable $e) {
            Log::error('[Portos] falha ao executar porto:criar', [
                'error' => $e->getMessage(),
            ]);
            $exitCode = -1;
        }

        $porto = Porto::where('slug', $slug)->first();
        Log::info('[Portos] busca por porto recém-criado', [
            'slug' => $slug,
            'encontrado' => (bool) $porto,
        ]);

        if (!$porto) {
            Log::warning('[Portos] porto não encontrado após comando; executando fallback via Eloquent', [
                'exit_code' => $exitCode,
            ]);

            try {
                $porto = Porto::create([
                    'nome' => $validated['nome'],
                    'slug' => $slug,
                    'dominio' => $validated['dominio'] ?? "{$slug}." . parse_url(config('app.url'), PHP_URL_HOST),
                    'email' => $validated['email'] ?? null,
                    'telefone' => $validated['telefone'] ?? null,
                    'endereco' => $validated['endereco'] ?? null,
                    'responsavel' => $validated['responsavel'] ?? null,
                    'telefone_responsavel' => $validated['telefone_responsavel'] ?? null,
                    'num_funcionarios' => $validated['num_funcionarios'] ?? null,
                    'db_nome' => $validated['db_nome'] ?? null,
                    'db_usuario' => $validated['db_usuario'] ?? null,
                    'db_senha' => $validated['db_senha'] ?? null,
                    'path' => $validated['path'] ?? null,
                    'worker_status' => $validated['worker_status'] ?? 'inactive',
                ]);

                Log::info('[Portos] porto criado via fallback', [
                    'porto_id' => $porto->id,
                    'slug' => $porto->slug,
                ]);
            } catch (\Throwable $e) {
                Log::error('[Portos] fallback create falhou', ['error' => $e->getMessage()]);
                return back()
                    ->withInput()
                    ->with('error', 'Falha ao criar porto (fallback): ' . $e->getMessage());
            }
        }

        // Atualiza campos extras caso tenha vindo do comando
        $porto->email = $validated['email'] ?? $porto->email;
        $porto->telefone = $validated['telefone'] ?? $porto->telefone;
        $porto->endereco = $validated['endereco'] ?? $porto->endereco;
        $porto->responsavel = $validated['responsavel'] ?? $porto->responsavel;
        $porto->telefone_responsavel = $validated['telefone_responsavel'] ?? $porto->telefone_responsavel;
        $porto->num_funcionarios = $validated['num_funcionarios'] ?? $porto->num_funcionarios;

        if (!$porto->dominio) {
            $porto->dominio = "{$slug}." . parse_url(config('app.url'), PHP_URL_HOST);
        }

        if (!$porto->worker_status) {
            $porto->worker_status = $validated['worker_status'] ?? 'inactive';
        }

        $porto->save();
        Log::info('[Portos] porto persistido com sucesso', ['porto_id' => $porto->id]);

        $sanitized = collect($validated)->except(['db_usuario', 'db_senha'])->all();

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'create',
            'auditable_type' => 'App\\Models\\Porto',
            'auditable_id' => $porto->id,
            'description' => "Criou porto: {$porto->nome} ({$porto->dominio})",
            'new_values' => $sanitized,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
        ]);

        return redirect()->route('portos.index')->with('success', 'Porto cadastrado com sucesso!');
    }

    /**
     * Visualização de porto
     */
    public function show(Porto $porto)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'view',
            'auditable_type' => 'App\\Models\\Porto',
            'auditable_id' => $porto->id,
            'description' => "Visualizou porto: {$porto->nome}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return view('portos.show', compact('porto'));
    }

    /**
     * Form de edição
     */
    public function edit(Porto $porto)
    {
        return view('portos.edit', compact('porto'));
    }

    /**
     * Atualização de porto
     */
    public function update(Request $request, Porto $porto)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('portos', 'slug')->ignore($porto->id)],
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:50',
            'endereco' => 'nullable|string|max:255',
            'responsavel' => 'nullable|string|max:255',
            'telefone_responsavel' => 'nullable|string|max:50',
            'num_funcionarios' => 'nullable|integer|min:0',
            'dominio' => ['string', 'max:255', Rule::unique('portos', 'dominio')->ignore($porto->id)],
            'db_nome' => 'nullable|string|max:255',
            'db_usuario' => 'nullable|string|max:255',
            'db_senha' => 'nullable|string|max:255',
            'path' => 'nullable|string|max:255',
            'worker_status' => 'required|string|in:inactive,active',
        ]);

        // Não sobrescrever db_senha se não enviada
        if (!filled($validated['db_senha'] ?? null)) {
            unset($validated['db_senha']);
        }

        $before = $porto->only(array_keys($validated));

        $porto->update($validated);

        $sanitizedNew = collect($validated)->except(['db_usuario', 'db_senha'])->all();
        $sanitizedBefore = collect($before)->except(['db_usuario', 'db_senha'])->all();

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'update',
            'auditable_type' => 'App\\Models\\Porto',
            'auditable_id' => $porto->id,
            'description' => "Atualizou porto: {$porto->nome}",
            'new_values' => $sanitizedNew,
            'changes' => json_encode(['before' => $sanitizedBefore, 'after' => $sanitizedNew]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('portos.index')->with('success', 'Porto atualizado com sucesso!');
    }

    /**
     * Exclusão de porto
     */
    public function destroy(Porto $porto)
    {
        // Dispara remoção de recursos no WHM/cPanel
        try {
            $args = [
            'slug' => $porto->slug,
            'dominio' => $porto->dominio,
            'path' => $porto->path,
            'db_nome' => $porto->db_nome,
            'db_usuario' => $porto->db_usuario,
        ];

        $exitCode = \Illuminate\Support\Facades\Artisan::call('porto:remover', $args);
        $output = \Illuminate\Support\Facades\Artisan::output();

        \Illuminate\Support\Facades\Log::info('[Portos] porto:remover executado', [
            'porto_id' => $porto->id,
            'args' => $args,
            'exit_code' => $exitCode,
            'output' => $output,
        ]);

        // Garante: primeiro remover no cPanel; só então excluir no Laravel
        if ($exitCode !== 0) {
            \Illuminate\Support\Facades\Log::warning('[Portos] remoção no WHM/cPanel falhou; abortando exclusão do registro', [
                'porto_id' => $porto->id,
                'args' => $args,
                'exit_code' => $exitCode,
                'output' => $output,
            ]);
            return redirect()
                ->route('portos.index')
                ->with('error', 'Falha ao remover recursos no cPanel. O porto não foi excluído.');
        }
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('[Portos] erro ao remover recursos WHM; abortando exclusão do registro', [
            'porto_id' => $porto->id,
            'error' => $e->getMessage(),
        ]);
        return redirect()
            ->route('portos.index')
            ->with('error', 'Erro ao remover recursos no cPanel. O porto não foi excluído.');
    }

    // Remoção local após sucesso no cPanel/WHM
    $nome = $porto->nome;
    $porto->delete();

    \App\Models\AuditLog::create([
        'user_id' => request()->user()->id,
        'event' => 'delete',
        'auditable_type' => 'App\\Models\\Porto',
        'auditable_id' => $porto->id,
        'description' => "Excluiu porto: {$nome}",
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent()
    ]);

        return redirect()->route('portos.index')->with('success', 'Porto excluído e recursos de infraestrutura removidos.');
    }
}