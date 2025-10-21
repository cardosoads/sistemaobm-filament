<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Limpa o cache de permissões para evitar inconsistências
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = config('permission.defaults.guard', 'web');

        // Criar roles
        $administrador = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => $guard]);
        $gerente = Role::firstOrCreate(['name' => 'Gerente', 'guard_name' => $guard]);
        $orcamento = Role::firstOrCreate(['name' => 'Orçamento', 'guard_name' => $guard]);
        $fornecedor = Role::firstOrCreate(['name' => 'Fornecedor', 'guard_name' => $guard]);
        $recursosHumanos = Role::firstOrCreate(['name' => 'Recursos Humanos', 'guard_name' => $guard]);
        $frotas = Role::firstOrCreate(['name' => 'Frotas', 'guard_name' => $guard]);

        // Administrador: todas as permissões existentes
        $administrador->givePermissionTo(\Spatie\Permission\Models\Permission::all()->pluck('name')->toArray());

        // Gerente: amplo (sem deletar colaboradores/frotas)
        $gerente->syncPermissions([
            // orçamentos
            'orcamentos.ver', 'orcamentos.criar', 'orcamentos.editar', 'orcamentos.deletar', 'orcamentos.exportar_pdf',
            // obms
            'obms.ver', 'obms.criar', 'obms.editar', 'obms.deletar',
            // colaboradores
            'colaboradores.ver', 'colaboradores.criar', 'colaboradores.editar',
            // frotas
            'frotas.ver', 'frotas.criar', 'frotas.editar',
        ]);

        // Orçamento: foco em orçamentos (sem deletar)
        $orcamento->syncPermissions([
            'orcamentos.ver', 'orcamentos.criar', 'orcamentos.editar', 'orcamentos.exportar_pdf',
        ]);

        // Fornecedor: acesso limitado a orçamentos
        $fornecedor->syncPermissions([
            'orcamentos.ver', 'orcamentos.exportar_pdf',
        ]);

        // Recursos Humanos: gestão de colaboradores (sem deletar) e visão de OBMs
        $recursosHumanos->syncPermissions([
            'colaboradores.ver', 'colaboradores.criar', 'colaboradores.editar',
            'obms.ver',
        ]);

        // Frotas: gestão de frotas (sem deletar) e visão de OBMs
        $frotas->syncPermissions([
            'frotas.ver', 'frotas.criar', 'frotas.editar',
            'obms.ver',
        ]);
    }
}