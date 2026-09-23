<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar Permissions
        $permissions = [
            // Usuários
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.manage_roles',

            // Roles (Funções)
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            
            // Embarcações
            'embarcacoes.view',
            'embarcacoes.create',
            'embarcacoes.edit',
            'embarcacoes.delete',
            'embarcacoes.approve',
            
            // Declarações
            'declaracoes.view',
            'declaracoes.create',
            'declaracoes.edit',
            'declaracoes.delete',
            'declaracoes.approve',
            'declaracoes.reject',
            
            // Pedidos
            'pedidos.view',
            'pedidos.create',
            'pedidos.edit',
            'pedidos.delete',
            'pedidos.approve',
            'pedidos.reject',
            
            // Alertas
            'alertas.view',
            'alertas.create',
            'alertas.edit',
            'alertas.delete',
            'alertas.manage',
            
            // Incidentes
            'incidentes.view',
            'incidentes.create',
            'incidentes.edit',
            'incidentes.delete',
            'incidentes.investigate',
            
            // Infrações Ambientais
            'infracoes.view',
            'infracoes.create',
            'infracoes.edit',
            'infracoes.delete',
            'infracoes.process',
            
            // Inspeções
            'inspecoes.view',
            'inspecoes.create',
            'inspecoes.edit',
            'inspecoes.delete',
            'inspecoes.schedule',
            
            // Produtos
            'produtos.view',
            'produtos.create',
            'produtos.edit',
            'produtos.delete',
            'produtos.manage',
            
            // Movimentos
            'movimentos.view',
            'movimentos.create',
            'movimentos.edit',
            'movimentos.delete',
            'movimentos.track',
            
            // Relatórios
            'relatorios.view',
            'relatorios.create',
            'relatorios.export',
            'relatorios.advanced',
            
            // Configurações
            'configuracoes.view',
            'configuracoes.edit',
            'configuracoes.system',
            
            // Auditoria
            'auditoria.view',
            'auditoria.export',
            
            // Dashboard
            'dashboard.admin',
            'dashboard.concessionaria',
            'dashboard.operador',

            // Embarcações Concessionária
            'embarcacoes_concessionaria.view',
            'embarcacoes_concessionaria.create',
            'embarcacoes_concessionaria.edit',
            'embarcacoes_concessionaria.delete',
            'embarcacoes_concessionaria.approve',
            'embarcacoes_concessionaria.manage',

            // Concessionárias
            'concessionarias.view',
            'concessionarias.create',
            'concessionarias.edit',
            'concessionarias.delete',
            'concessionarias.approve',
            'concessionarias.manage',
            'concessionarias.suspend',
            'concessionarias.reactivate',

            // Terminais
            'terminais.view',
            'terminais.create',
            'terminais.edit',
            'terminais.delete',
            'terminais.approve',
            'terminais.manage',
            'terminais.assign_concessionaria',
            'terminais.operations',
            
           

           
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Criar Roles
        
        // 1. Admin (Super Administrador)
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());
        
        // 2. Supervisor Portuário
        $supervisorPortuario = Role::firstOrCreate(['name' => 'supervisor_portuario']);
        $supervisorPortuario->givePermissionTo([
            'users.view', 'users.create', 'users.edit',
            'embarcacoes.view', 'embarcacoes.approve',
            'declaracoes.view', 'declaracoes.approve', 'declaracoes.reject',
            'pedidos.view', 'pedidos.approve', 'pedidos.reject',
            'alertas.view', 'alertas.manage',
            'incidentes.view', 'incidentes.investigate',
            'infracoes.view', 'infracoes.process',
            'inspecoes.view', 'inspecoes.schedule',
            'produtos.view', 'produtos.manage',
            'movimentos.view', 'movimentos.track',
            'terminais.view', 'terminais.manage', 'terminais.operations',
            'concessionarias.view', 'concessionarias.manage',
            'relatorios.view', 'relatorios.create', 'relatorios.export',
            'configuracoes.view', 'configuracoes.edit',
            'auditoria.view',
            'dashboard.admin'
        ]);
        
        // 3. Inspector ISPS
        $inspectorISPS = Role::firstOrCreate(['name' => 'inspector_isps']);
        $inspectorISPS->givePermissionTo([
            'embarcacoes.view',
            'declaracoes.view',
            'pedidos.view',
            'alertas.view', 'alertas.create',
            'incidentes.view', 'incidentes.create', 'incidentes.edit',
            'inspecoes.view', 'inspecoes.create', 'inspecoes.edit',
            'infracoes.view', 'infracoes.create',
            'movimentos.view',
            'relatorios.view', 'relatorios.create',
            'dashboard.operador'
        ]);
        
        // 4. Inspector Ambiental
        $inspectorAmbiental = Role::firstOrCreate(['name' => 'inspector_ambiental']);
        $inspectorAmbiental->givePermissionTo([
            'embarcacoes.view',
            'declaracoes.view',
            'alertas.view', 'alertas.create',
            'incidentes.view', 'incidentes.create', 'incidentes.edit',
            'infracoes.view', 'infracoes.create', 'infracoes.edit', 'infracoes.process',
            'inspecoes.view', 'inspecoes.create', 'inspecoes.edit',
            'produtos.view',
            'movimentos.view',
            'relatorios.view', 'relatorios.create',
            'dashboard.operador'
        ]);
        
        // 5. Inspector do Cais
        $inspectorCais = Role::firstOrCreate(['name' => 'inspector_cais']);
        $inspectorCais->givePermissionTo([
            'embarcacoes.view',
            'declaracoes.view',
            'pedidos.view',
            'alertas.view', 'alertas.create',
            'incidentes.view', 'incidentes.create',
            'inspecoes.view', 'inspecoes.create', 'inspecoes.edit',
            'produtos.view',
            'movimentos.view', 'movimentos.create', 'movimentos.edit',
            'terminais.view', 'terminais.operations',
            'relatorios.view', 'relatorios.create',
            'dashboard.operador'
        ]);
        
        // 6. Técnico Comercial
        $tecnicoComercial = Role::firstOrCreate(['name' => 'tecnico_comercial']);
        $tecnicoComercial->givePermissionTo([
            'embarcacoes.view',
            'declaracoes.view', 'declaracoes.create', 'declaracoes.edit',
            'pedidos.view', 'pedidos.create', 'pedidos.edit',
            'produtos.view', 'produtos.create', 'produtos.edit',
            'movimentos.view', 'movimentos.create', 'movimentos.edit',
            'terminais.view',
            'concessionarias.view',
            'relatorios.view', 'relatorios.create',
            'dashboard.operador'
        ]);
        
        // 7. Concessionária
        $concessionaria = Role::firstOrCreate(['name' => 'concessionaria']);
        $concessionaria->givePermissionTo([
            'users.view', 'users.create', 'users.edit', // apenas da própria concessionária
            'embarcacoes.view', 'embarcacoes.create', 'embarcacoes.edit',
            'embarcacoes_concessionaria.view', 'embarcacoes_concessionaria.create', 'embarcacoes_concessionaria.edit',
            'declaracoes.view', 'declaracoes.create', 'declaracoes.edit',
            'pedidos.view', 'pedidos.create', 'pedidos.edit',
            'produtos.view', 'produtos.create', 'produtos.edit',
            'movimentos.view', 'movimentos.create', 'movimentos.edit',
            'terminais.view',
            'relatorios.view', 'relatorios.create',
            'dashboard.concessionaria'
        ]);

        // Criar usuário Admin padrão
        $adminUser = User::firstOrCreate(
            ['email' => 'manuel.neto280690@gmail.com'],
            [
                'id' => Str::uuid(),
                'name' => 'Administrador do Sistema',
                'password' => Hash::make('admin123'),
                'telefone' => '+244 900 000 000',
                'cargo' => 'Administrador',
                'departamento' => 'TI',
                'is_active' => true,
                'email_verified_at' => now(),
                'two_factor_enabled' => false,
            ]
        );
        
        $adminUser->assignRole('admin');
    }
}