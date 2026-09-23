<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users.view', ['only' => ['index', 'show']]);
        $this->middleware('permission:users.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:users.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:users.delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of users.
     */
    public function index()
    {
        $users = User::with('roles')
            ->orderBy('name')
            ->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'telefone' => 'nullable|string|max:20',
            'cargo' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'roles' => 'array',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'two_factor_method' => 'in:email,authenticator',
        ]);
    
        try {
            DB::beginTransaction();
    
            // Determinar configuração de 2FA
            $twoFactorEnabled = $request->boolean('two_factor_enabled', false);
            $twoFactorMethod = $request->two_factor_method ?? 'email';
            $twoFactorSetupPending = false;
            
            // Se 2FA está habilitado e método é authenticator, marcar como pendente
            if ($twoFactorEnabled && $twoFactorMethod === 'authenticator') {
                $twoFactorEnabled = false; // Não ativar ainda
                $twoFactorSetupPending = true; // Marcar como pendente
            }
    
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telefone' => $request->telefone,
                'cargo' => $request->cargo,
                'departamento' => $request->departamento,
                'is_active' => $request->boolean('is_active', true),
                'two_factor_enabled' => $twoFactorEnabled,
                'two_factor_method' => $twoFactorMethod,
                'two_factor_setup_pending' => $twoFactorSetupPending,
            ]);
    
            if ($request->has('roles')) {
                $user->assignRole($request->roles);
            }

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'create',
                'auditable_type' => 'User',
                'auditable_id' => $user->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name'),
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'Usuário criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao criar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load('roles.permissions');
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        
        return view('users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'telefone' => 'nullable|string|max:20',
            'cargo' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'roles' => 'array',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'two_factor_method' => 'nullable|in:email,authenticator',
        ]);

        try {
            DB::beginTransaction();

            $oldValues = [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'is_active' => $user->is_active,
                'two_factor_enabled' => $user->two_factor_enabled,
                'two_factor_method' => $user->two_factor_method,
            ];

            // Determinar configuração de 2FA
            $twoFactorEnabled = $request->boolean('two_factor_enabled', false);
            $twoFactorMethod = $request->two_factor_method ?? 'email';
            $twoFactorSetupPending = false;
            
            // Se 2FA está habilitado e método é authenticator, marcar como pendente
            if ($twoFactorEnabled && $twoFactorMethod === 'authenticator') {
                $twoFactorEnabled = false; // Não ativar ainda
                $twoFactorSetupPending = true; // Marcar como pendente
            }

            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'telefone' => $request->telefone,
                'cargo' => $request->cargo,
                'departamento' => $request->departamento,
                'is_active' => $request->boolean('is_active', false),
                'two_factor_enabled' => $twoFactorEnabled,
                'two_factor_method' => $twoFactorMethod,
                'two_factor_setup_pending' => $twoFactorSetupPending,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);
            
            // Sincronizar roles - converter IDs para nomes se necessário
            if ($request->has('roles')) {
                // Verificar se são IDs ou nomes
                $roles = $request->roles;
                if (is_numeric($roles[0] ?? null)) {
                    // São IDs, converter para nomes
                    $roleNames = Role::whereIn('id', $roles)->pluck('name')->toArray();
                    $user->syncRoles($roleNames);
                } else {
                    // São nomes, usar diretamente
                    $user->syncRoles($roles);
                }
            } else {
                $user->syncRoles([]);
            }

            $newValues = [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'is_active' => $user->is_active,
                'two_factor_enabled' => $user->two_factor_enabled,
                'two_factor_method' => $user->two_factor_method,
            ];

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'User',
                'auditable_id' => $user->id,
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode($newValues),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'Usuário atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user)
    {
        // Prevenir exclusão do próprio usuário
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Você não pode excluir sua própria conta.');
        }

        try {
            DB::beginTransaction();

            $oldValues = [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
            ];

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'delete',
                'auditable_type' => 'User',
                'auditable_id' => $user->id,
                'old_values' => json_encode($oldValues),
                'new_values' => null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            $user->delete();

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'Usuário excluído com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao excluir usuário: ' . $e->getMessage());
        }
    }
}