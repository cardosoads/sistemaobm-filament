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
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Executar seeders na ordem correta (respeitando dependências)
        $this->call([
            // Seeders independentes primeiro
            BaseSeeder::class,
            CombustivelSeeder::class,
            TipoVeiculoSeeder::class,
            MarcaSeeder::class,
            ImpostoSeeder::class,
            
            // Seeders que dependem de outros
            FrotaSeeder::class,          // Depende de TipoVeiculo
            RecursoHumanoSeeder::class,  // Depende de Base
            GrupoImpostoSeeder::class,   // Depende de Imposto
        ]);

        $this->command->info('Todos os seeders foram executados com sucesso!');
    }
}
