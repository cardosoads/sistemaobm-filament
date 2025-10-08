<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoVeiculo;

class TipoVeiculoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposVeiculos = [
            [
                'codigo' => 'PASSEIO',
                'consumo_km_litro' => 12.5,
                'tipo_combustivel' => 'Flex',
                'descricao' => 'Veículo de passeio para uso geral',
                'active' => true,
            ],
            [
                'codigo' => 'P-CARGO',
                'consumo_km_litro' => 10.8,
                'tipo_combustivel' => 'Flex',
                'descricao' => 'Veículo de passeio com capacidade de carga',
                'active' => true,
            ],
            [
                'codigo' => 'CAMINHONETE',
                'consumo_km_litro' => 9.2,
                'tipo_combustivel' => 'Diesel',
                'descricao' => 'Caminhonete para transporte de materiais',
                'active' => true,
            ],
            [
                'codigo' => 'VAN',
                'consumo_km_litro' => 8.5,
                'tipo_combustivel' => 'Diesel',
                'descricao' => 'Van para transporte de pessoal',
                'active' => true,
            ],
            [
                'codigo' => 'MICRO-ONIBUS',
                'consumo_km_litro' => 7.8,
                'tipo_combustivel' => 'Diesel',
                'descricao' => 'Micro-ônibus para transporte coletivo',
                'active' => true,
            ],
            [
                'codigo' => 'ONIBUS',
                'consumo_km_litro' => 6.2,
                'tipo_combustivel' => 'Diesel',
                'descricao' => 'Ônibus para transporte de grande capacidade',
                'active' => true,
            ],
            [
                'codigo' => 'CAMINHAO-LEVE',
                'consumo_km_litro' => 8.0,
                'tipo_combustivel' => 'Diesel',
                'descricao' => 'Caminhão leve para cargas pequenas',
                'active' => true,
            ],
            [
                'codigo' => 'CAMINHAO-MEDIO',
                'consumo_km_litro' => 6.5,
                'tipo_combustivel' => 'Diesel',
                'descricao' => 'Caminhão médio para cargas moderadas',
                'active' => true,
            ],
            [
                'codigo' => 'CAMINHAO-PESADO',
                'consumo_km_litro' => 4.8,
                'tipo_combustivel' => 'Diesel',
                'descricao' => 'Caminhão pesado para grandes cargas',
                'active' => true,
            ],
            [
                'codigo' => 'MOTOCICLETA',
                'consumo_km_litro' => 35.0,
                'tipo_combustivel' => 'Gasolina',
                'descricao' => 'Motocicleta para deslocamentos rápidos',
                'active' => true,
            ],
        ];

        foreach ($tiposVeiculos as $tipoVeiculo) {
            TipoVeiculo::firstOrCreate(
                ['codigo' => $tipoVeiculo['codigo']],
                $tipoVeiculo
            );
        }
    }
}
