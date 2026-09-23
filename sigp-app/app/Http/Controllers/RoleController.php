<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\AuditLog;
use Carbon\Carbon;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:roles.view')->only(['index', 'show']);
        $this->middleware('permission:roles.create')->only(['create', 'store']);
        $this->middleware('permission:roles.edit')->only(['edit', 'update']);
        $this->middleware('permission:roles.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the roles.
     */
    public function index(Request $request)
    {
        $query = Role::query();

        // Busca por nome
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Ordenação
        $sortField = $request->sort_field ?? 'name';
        $sortDirection = $request->sort_direction ?? 'asc';
        $query->orderBy($sortField, $sortDirection);

        $roles = $query->paginate(10);

        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });
        
        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
            
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'create',  // Alterado de 'action' para 'event'
                'auditable_type' => 'Role', // Alterado de 'model_type' para 'auditable_type'
                'auditable_id' => $role->id, // Alterado de 'model_id' para 'auditable_id'
                'old_values' => null,
                'new_values' => json_encode([
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name'),
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            return redirect()->route('roles.index')
                ->with('success', 'Função criada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao criar função: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load('permissions');
        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });
        
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);
    
        try {
            DB::beginTransaction();
    
            $oldValues = [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ];
    
            $role->name = $request->name;
            $role->save();
            
            if ($request->has('permissions')) {
                // Buscar as permissões pelos IDs recebidos
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions([]);
            }

            $newValues = [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ];

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',  // Alterado de 'action' para 'event'
                'auditable_type' => 'Role', // Alterado de 'model_type' para 'auditable_type'
                'auditable_id' => $role->id, // Alterado de 'model_id' para 'auditable_id'
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode($newValues),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            return redirect()->route('roles.index')
                ->with('success', 'Função atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao atualizar função: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Request $request, Role $role)
    {
        try {
            DB::beginTransaction();

            $oldValues = [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ];

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'delete',  // Alterado de 'action' para 'event'
                'auditable_type' => 'Role', // Alterado de 'model_type' para 'auditable_type'
                'auditable_id' => $role->id, // Alterado de 'model_id' para 'auditable_id'
                'old_values' => json_encode($oldValues),
                'new_values' => null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            $role->delete();

            DB::commit();

            return redirect()->route('roles.index')
                ->with('success', 'Função excluída com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao excluir função: ' . $e->getMessage());
        }
    }
}