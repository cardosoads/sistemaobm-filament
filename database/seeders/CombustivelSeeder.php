<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Combustivel;
use App\Models\Base;

class CombustivelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar bases existentes
        $bases = Base::where('status', true)->get();
        
        if ($bases->isEmpty()) {
            $this->command->warn('Nenhuma base encontrada. Execute o BaseSeeder primeiro.');
            return;
        }

        $convenios = [
            'Petrobras',
            'Shell',
            'Ipiranga',
            'Raízen',
            'BR Distribuidora',
            'Ale Combustíveis',
        ];

        $precos = [
            'Petrobras' => [
                'gasolina' => 5.89,
                'etanol' => 3.99,
                'diesel' => 5.45,
            ],
            'Shell' => [
                'gasolina' => 6.15,
                'etanol' => 4.10,
                'diesel' => 5.60,
            ],
            'Ipiranga' => [
                'gasolina' => 5.95,
                'etanol' => 4.05,
                'diesel' => 5.50,
            ],
            'Raízen' => [
                'gasolina' => 5.85,
                'etanol' => 3.95,
                'diesel' => 5.40,
            ],
            'BR Distribuidora' => [
                'gasolina' => 5.92,
                'etanol' => 4.02,
                'diesel' => 5.48,
            ],
            'Ale Combustíveis' => [
                'gasolina' => 5.80,
                'etanol' => 3.90,
                'diesel' => 5.35,
            ],
        ];

        // Criar combustíveis para cada base e convênio
        foreach ($bases as $base) {
            foreach ($convenios as $convenio) {
                // Gasolina
                Combustivel::firstOrCreate(
                    [
                        'base_id' => $base->id,
                        'convenio' => $convenio . ' - Gasolina',
                    ],
                    [
                        'preco_litro' => $precos[$convenio]['gasolina'],
                        'active' => true,
                    ]
                );

                // Etanol
                Combustivel::firstOrCreate(
                    [
                        'base_id' => $base->id,
                        'convenio' => $convenio . ' - Etanol',
                    ],
                    [
                        'preco_litro' => $precos[$convenio]['etanol'],
                        'active' => true,
                    ]
                );

                // Diesel
                Combustivel::firstOrCreate(
                    [
                        'base_id' => $base->id,
                        'convenio' => $convenio . ' - Diesel',
                    ],
                    [
                        'preco_litro' => $precos[$convenio]['diesel'],
                        'active' => true,
                    ]
                );
            }
        }
    }
}
