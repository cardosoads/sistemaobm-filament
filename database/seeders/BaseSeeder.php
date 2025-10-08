<?php

namespace Database\Seeders;

use App\Models\Base;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bases = [
            [
                'uf' => 'SP',
                'base' => 'São Paulo',
                'regional' => 'Sudeste',
                'sigla' => 'SP01',
                'status' => true,
            ],
            [
                'uf' => 'RJ',
                'base' => 'Rio de Janeiro',
                'regional' => 'Sudeste',
                'sigla' => 'RJ01',
                'status' => true,
            ],
            [
                'uf' => 'MG',
                'base' => 'Belo Horizonte',
                'regional' => 'Sudeste',
                'sigla' => 'MG01',
                'status' => true,
            ],
            [
                'uf' => 'RS',
                'base' => 'Porto Alegre',
                'regional' => 'Sul',
                'sigla' => 'RS01',
                'status' => true,
            ],
            [
                'uf' => 'BA',
                'base' => 'Salvador',
                'regional' => 'Nordeste',
                'sigla' => 'BA01',
                'status' => true,
            ],
        ];

        foreach ($bases as $baseData) {
            Base::firstOrCreate(
                ['sigla' => $baseData['sigla']],
                $baseData
            );
        }
    }
}
