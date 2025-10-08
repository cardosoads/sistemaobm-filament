<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Frota;
use App\Models\TipoVeiculo;

class FrotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar tipos de veículos existentes
        $tipoVeiculoIds = TipoVeiculo::pluck('id')->toArray();
        
        if (empty($tipoVeiculoIds)) {
            $this->command->warn('Nenhum tipo de veículo encontrado. Execute o TipoVeiculoSeeder primeiro.');
            return;
        }

        $frotas = [
            [
                'tipo_veiculo_id' => $tipoVeiculoIds[0] ?? 1,
                'fipe' => 45000.00,
                'percentual_aluguel' => 2.67, // 1200/45000 * 100
                'rastreador' => 150.00,
                'percentual_provisoes_avarias' => 5.00,
                'percentual_provisao_desmobilizacao' => 3.00,
                'percentual_provisao_rac' => 2.00,
                'provisao_diaria_rac' => 900.00,
                'active' => true,
            ],
            [
                'tipo_veiculo_id' => $tipoVeiculoIds[1] ?? $tipoVeiculoIds[0],
                'fipe' => 65000.00,
                'percentual_aluguel' => 2.77, // 1800/65000 * 100
                'rastreador' => 200.00,
                'percentual_provisoes_avarias' => 4.50,
                'percentual_provisao_desmobilizacao' => 2.50,
                'percentual_provisao_rac' => 1.80,
                'provisao_diaria_rac' => 1170.00,
                'active' => true,
            ],
            [
                'tipo_veiculo_id' => $tipoVeiculoIds[2] ?? $tipoVeiculoIds[0],
                'fipe' => 55000.00,
                'percentual_aluguel' => 2.18, // 1200/55000 * 100
                'rastreador' => 180.00,
                'percentual_provisoes_avarias' => 6.00,
                'percentual_provisao_desmobilizacao' => 4.00,
                'percentual_provisao_rac' => 2.20,
                'provisao_diaria_rac' => 1210.00,
                'active' => true,
            ],
            [
                'tipo_veiculo_id' => $tipoVeiculoIds[3] ?? $tipoVeiculoIds[0],
                'fipe' => 80000.00,
                'percentual_aluguel' => 2.50, // 2000/80000 * 100
                'rastreador' => 250.00,
                'percentual_provisoes_avarias' => 5.50,
                'percentual_provisao_desmobilizacao' => 3.50,
                'percentual_provisao_rac' => 1.90,
                'provisao_diaria_rac' => 1520.00,
                'active' => true,
            ],
            [
                'tipo_veiculo_id' => $tipoVeiculoIds[4] ?? $tipoVeiculoIds[0],
                'fipe' => 35000.00,
                'percentual_aluguel' => 2.86, // 1000/35000 * 100
                'rastreador' => 120.00,
                'percentual_provisoes_avarias' => 4.00,
                'percentual_provisao_desmobilizacao' => 2.00,
                'percentual_provisao_rac' => 1.50,
                'provisao_diaria_rac' => 525.00,
                'active' => true,
            ],
            [
                'tipo_veiculo_id' => $tipoVeiculoIds[0] ?? 1,
                'fipe' => 70000.00,
                'percentual_aluguel' => 2.14, // 1500/70000 * 100
                'rastreador' => 220.00,
                'percentual_provisoes_avarias' => 5.20,
                'percentual_provisao_desmobilizacao' => 3.20,
                'percentual_provisao_rac' => 2.10,
                'provisao_diaria_rac' => 1470.00,
                'active' => true,
            ],
            [
                'tipo_veiculo_id' => $tipoVeiculoIds[1] ?? $tipoVeiculoIds[0],
                'fipe' => 48000.00,
                'percentual_aluguel' => 3.13, // 1500/48000 * 100
                'rastreador' => 160.00,
                'percentual_provisoes_avarias' => 4.80,
                'percentual_provisao_desmobilizacao' => 2.80,
                'percentual_provisao_rac' => 1.70,
                'provisao_diaria_rac' => 816.00,
                'active' => true,
            ],
            [
                'tipo_veiculo_id' => $tipoVeiculoIds[2] ?? $tipoVeiculoIds[0],
                'fipe' => 60000.00,
                'percentual_aluguel' => 2.50, // 1500/60000 * 100
                'rastreador' => 190.00,
                'percentual_provisoes_avarias' => 5.80,
                'percentual_provisao_desmobilizacao' => 3.80,
                'percentual_provisao_rac' => 2.00,
                'provisao_diaria_rac' => 1200.00,
                'active' => true,
            ],
            [
                'tipo_veiculo_id' => $tipoVeiculoIds[3] ?? $tipoVeiculoIds[0],
                'fipe' => 42000.00,
                'percentual_aluguel' => 2.38, // 1000/42000 * 100
                'rastreador' => 140.00,
                'percentual_provisoes_avarias' => 3.50,
                'percentual_provisao_desmobilizacao' => 2.20,
                'percentual_provisao_rac' => 1.40,
                'provisao_diaria_rac' => 588.00,
                'active' => true,
            ],
            [
                'tipo_veiculo_id' => $tipoVeiculoIds[4] ?? $tipoVeiculoIds[0],
                'fipe' => 90000.00,
                'percentual_aluguel' => 2.78, // 2500/90000 * 100
                'rastreador' => 300.00,
                'percentual_provisoes_avarias' => 7.00,
                'percentual_provisao_desmobilizacao' => 4.50,
                'percentual_provisao_rac' => 2.50,
                'provisao_diaria_rac' => 2250.00,
                'active' => true,
            ],
        ];

        foreach ($frotas as $frotaData) {
            Frota::create($frotaData);
        }

        $this->command->info('Frotas criadas com sucesso!');
    }
}
