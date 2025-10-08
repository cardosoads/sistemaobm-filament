<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Marca;

class MarcaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $marcas = [
            [
                'marca' => 'Toyota',
                'mercado' => 'Nacional',
                'status' => true,
            ],
            [
                'marca' => 'Volkswagen',
                'mercado' => 'Nacional',
                'status' => true,
            ],
            [
                'marca' => 'Ford',
                'mercado' => 'Nacional',
                'status' => true,
            ],
            [
                'marca' => 'Chevrolet',
                'mercado' => 'Nacional',
                'status' => true,
            ],
            [
                'marca' => 'Fiat',
                'mercado' => 'Nacional',
                'status' => true,
            ],
            [
                'marca' => 'Honda',
                'mercado' => 'Importado',
                'status' => true,
            ],
            [
                'marca' => 'Hyundai',
                'mercado' => 'Nacional',
                'status' => true,
            ],
            [
                'marca' => 'Nissan',
                'mercado' => 'Nacional',
                'status' => true,
            ],
            [
                'marca' => 'Renault',
                'mercado' => 'Nacional',
                'status' => true,
            ],
            [
                'marca' => 'Peugeot',
                'mercado' => 'Nacional',
                'status' => false,
            ],
        ];

        foreach ($marcas as $marca) {
            Marca::firstOrCreate(
                ['marca' => $marca['marca']],
                $marca
            );
        }
    }
}
