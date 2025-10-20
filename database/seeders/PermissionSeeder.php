<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpa o cache de permissões para evitar inconsistências
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = config('permission.defaults.guard', 'web');

        $modules = [
            'orcamentos' => ['ver', 'criar', 'editar', 'deletar', 'exportar_pdf'],
            'obms' => ['ver', 'criar', 'editar', 'deletar'],
            'colaboradores' => ['ver', 'criar', 'editar', 'deletar'],
            'frotas' => ['ver', 'criar', 'editar', 'deletar'],
        ];

        $permissionNames = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $name = "$module.$action";
                $permissionNames[] = $name;
                Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => $guard,
                ]);
            }
        }

        // Roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $gestor = Role::firstOrCreate(['name' => 'gestor', 'guard_name' => $guard]);
        $operador = Role::firstOrCreate(['name' => 'operador', 'guard_name' => $guard]);

        // Admin: todas as permissões
        $admin->givePermissionTo($permissionNames);

        // Gestor: atuação ampla sem deletar colaboradores/frotas
        $gestorPermissions = [
            // orçamentos
            'orcamentos.ver', 'orcamentos.criar', 'orcamentos.editar', 'orcamentos.deletar', 'orcamentos.exportar_pdf',
            // obms
            'obms.ver', 'obms.criar', 'obms.editar', 'obms.deletar',
            // colaboradores
            'colaboradores.ver', 'colaboradores.criar', 'colaboradores.editar',
            // frotas
            'frotas.ver', 'frotas.criar', 'frotas.editar',
        ];
        $gestor->syncPermissions($gestorPermissions);

        // Operador: leitura e atuação em orçamentos (sem deletar)
        $operadorPermissions = [
            // orçamentos
            'orcamentos.ver', 'orcamentos.criar', 'orcamentos.editar', 'orcamentos.exportar_pdf',
            // obms
            'obms.ver',
            // colaboradores
            'colaboradores.ver',
            // frotas
            'frotas.ver',
        ];
        $operador->syncPermissions($operadorPermissions);
    }
}