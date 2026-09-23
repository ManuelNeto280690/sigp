<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use App\Models\AuditLog;
use Carbon\Carbon;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:permissions.view')->only(['index', 'show']);
        $this->middleware('permission:permissions.create')->only(['create', 'store']);
        $this->middleware('permission:permissions.edit')->only(['edit', 'update']);
        $this->middleware('permission:permissions.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the permissions.
     */
    public function index(Request $request)
    {
        $query = Permission::query();

        // Busca por nome
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Ordenação
        $sortField = $request->sort_field ?? 'name';
        $sortDirection = $request->sort_direction ?? 'asc';
        $query->orderBy($sortField, $sortDirection);

        // Agrupar permissões por módulo (primeiro segmento do nome)
        $permissions = $query->get()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        return view('permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        return view('permissions.create');
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        try {
            DB::beginTransaction();

            $permission = Permission::create(['name' => $request->name, 'guard_name' => 'web']);

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'create',  // Já está correto
                'auditable_type' => 'Permission', // Alterado de 'model_type' para 'auditable_type'
                'auditable_id' => $permission->id, // Alterado de 'model_id' para 'auditable_id'
                'old_values' => null,
                'new_values' => json_encode([
                    'name' => $permission->name,
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route('permissions.index')
                ->with('success', 'Permissão criada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao criar permissão: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission)
    {
        return view('permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        return view('permissions.edit', compact('permission'));
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ]);

        try {
            DB::beginTransaction();

            $oldValues = [
                'name' => $permission->name,
            ];

            $permission->name = $request->name;
            $permission->save();

            $newValues = [
                'name' => $permission->name,
            ];

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'update',
                'auditable_type' => 'Permission',
                'auditable_id' => $permission->id,
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode($newValues),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);

            DB::commit();

            return redirect()->route('permissions.index')
                ->with('success', 'Permissão atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao atualizar permissão: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Request $request, Permission $permission)
    {
        try {
            DB::beginTransaction();

            $oldValues = [
                'name' => $permission->name,
            ];

            // Log de auditoria
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'delete',
                'auditable_type' => 'Permission',
                'auditable_id' => $permission->id,
                'old_values' => json_encode($oldValues),
                'new_values' => null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);
            $permission->delete();

            DB::commit();

            return redirect()->route('permissions.index')
                ->with('success', 'Permissão excluída com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao excluir permissão: ' . $e->getMessage());
        }
    }
}