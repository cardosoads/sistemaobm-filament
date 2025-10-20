<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar usuário de teste
        $this->call([
            UserSeeder::class,
            // Seeders independentes primeiro
            BaseSeeder::class,
            CombustivelSeeder::class,
            TipoVeiculoSeeder::class,
            MarcaSeeder::class,
            ImpostoSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            
            // Seeders que dependem de outros
            FrotaSeeder::class,          // Depende de TipoVeiculo
            RecursoHumanoSeeder::class,  // Depende de Base
            GrupoImpostoSeeder::class,   // Depende de Imposto
        ]);

        // Atribuir role 'Administrador' a todos os usuários existentes
        foreach (\App\Models\User::all() as $user) {
            if (! $user->hasRole('Administrador')) {
                $user->assignRole('Administrador');
            }
        }

        $this->command->info('Todos os seeders foram executados com sucesso!');
    }
}
