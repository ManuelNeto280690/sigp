<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    /**
     * Verificar se o usuário tem uma permission específica
     */
    public static function can(string $permission): bool
    {
        return Auth::check() && Auth::user()->can($permission);
    }
    
    /**
     * Verificar se o usuário tem qualquer uma das permissions
     */
    public static function canAny(array $permissions): bool
    {
        if (!Auth::check()) {
            return false;
        }
        
        foreach ($permissions as $permission) {
            if (Auth::user()->can($permission)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Verificar se o usuário tem um role específico
     */
    public static function hasRole(string $role): bool
    {
        return Auth::check() && Auth::user()->hasRole($role);
    }
    
    /**
     * Verificar se o usuário tem qualquer um dos roles
     */
    public static function hasAnyRole(array $roles): bool
    {
        return Auth::check() && Auth::user()->hasAnyRole($roles);
    }
    
    /**
     * Verificar se o usuário é da ANTAQ
     */
    public static function isAntaq(): bool
    {
        return self::hasAnyRole(['super_admin', 'admin_antaq', 'operador_antaq', 'fiscal', 'auditor']);
    }
    
    /**
     * Verificar se o usuário é de Concessionária
     */
    public static function isConcessionaria(): bool
    {
        return self::hasAnyRole(['admin_concessionaria', 'operador_concessionaria']);
    }
    
    /**
     * Verificar se o usuário pode gerenciar outros usuários
     */
    public static function canManageUsers(): bool
    {
        return self::can('users.manage_roles');
    }
    
    /**
     * Verificar se o usuário pode aprovar declarações
     */
    public static function canApproveDeclaracoes(): bool
    {
        return self::can('declaracoes.approve');
    }
    
    /**
     * Verificar se o usuário pode acessar relatórios avançados
     */
    public static function canAccessAdvancedReports(): bool
    {
        return self::can('relatorios.advanced');
    }
}