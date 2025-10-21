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

        // Removido: criação e sincronização de roles antigas ('admin', 'gestor', 'operador').
        // As novas roles e suas permissões são tratadas no RoleSeeder.
    }
}