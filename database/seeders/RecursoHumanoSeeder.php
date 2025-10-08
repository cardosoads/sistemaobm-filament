<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RecursoHumano;
use App\Models\Base;

class RecursoHumanoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar bases existentes
        $baseIds = Base::pluck('id')->toArray();
        
        if (empty($baseIds)) {
            $this->command->warn('Nenhuma base encontrada. Execute o BaseSeeder primeiro.');
            return;
        }

        $recursosHumanos = [
            [
                'tipo_contratacao' => 'CLT',
                'cargo' => 'Motorista',
                'base_id' => $baseIds[0] ?? 1,
                'base_salarial' => 2500.00,
                'salario_base' => 2500.00,
                'insalubridade' => 250.00,
                'periculosidade' => 500.00,
                'horas_extras' => 300.00,
                'adicional_noturno' => 200.00,
                'extras' => 150.00,
                'vale_transporte' => 180.00,
                'beneficios' => 400.00,
                'encargos_sociais' => 1200.00,
                'custo_total_mao_obra' => 5680.00,
                'percentual_encargos' => 48.00,
                'percentual_beneficios' => 16.00,
                'active' => true,
            ],
            [
                'tipo_contratacao' => 'CLT',
                'cargo' => 'Mecânico',
                'base_id' => $baseIds[0] ?? 1,
                'base_salarial' => 3200.00,
                'salario_base' => 3200.00,
                'insalubridade' => 320.00,
                'periculosidade' => 640.00,
                'horas_extras' => 400.00,
                'adicional_noturno' => 0.00,
                'extras' => 200.00,
                'vale_transporte' => 180.00,
                'beneficios' => 500.00,
                'encargos_sociais' => 1536.00,
                'custo_total_mao_obra' => 6976.00,
                'percentual_encargos' => 48.00,
                'percentual_beneficios' => 15.63,
                'active' => true,
            ],
            [
                'tipo_contratacao' => 'PJ',
                'cargo' => 'Consultor',
                'base_id' => $baseIds[1] ?? $baseIds[0],
                'base_salarial' => 8000.00,
                'salario_base' => 8000.00,
                'insalubridade' => 0.00,
                'periculosidade' => 0.00,
                'horas_extras' => 0.00,
                'adicional_noturno' => 0.00,
                'extras' => 0.00,
                'vale_transporte' => 0.00,
                'beneficios' => 0.00,
                'encargos_sociais' => 0.00,
                'custo_total_mao_obra' => 8000.00,
                'percentual_encargos' => 0.00,
                'percentual_beneficios' => 0.00,
                'active' => true,
            ],
            [
                'tipo_contratacao' => 'CLT',
                'cargo' => 'Supervisor',
                'base_id' => $baseIds[0] ?? 1,
                'base_salarial' => 4500.00,
                'salario_base' => 4500.00,
                'insalubridade' => 0.00,
                'periculosidade' => 900.00,
                'horas_extras' => 600.00,
                'adicional_noturno' => 300.00,
                'extras' => 250.00,
                'vale_transporte' => 180.00,
                'beneficios' => 700.00,
                'encargos_sociais' => 2160.00,
                'custo_total_mao_obra' => 9490.00,
                'percentual_encargos' => 48.00,
                'percentual_beneficios' => 15.56,
                'active' => true,
            ],
            [
                'tipo_contratacao' => 'CLT',
                'cargo' => 'Auxiliar Administrativo',
                'base_id' => $baseIds[1] ?? $baseIds[0],
                'base_salarial' => 1800.00,
                'salario_base' => 1800.00,
                'insalubridade' => 0.00,
                'periculosidade' => 0.00,
                'horas_extras' => 150.00,
                'adicional_noturno' => 0.00,
                'extras' => 100.00,
                'vale_transporte' => 180.00,
                'beneficios' => 300.00,
                'encargos_sociais' => 864.00,
                'custo_total_mao_obra' => 3394.00,
                'percentual_encargos' => 48.00,
                'percentual_beneficios' => 16.67,
                'active' => true,
            ],
            [
                'tipo_contratacao' => 'CLT',
                'cargo' => 'Operador de Máquinas',
                'base_id' => $baseIds[0] ?? 1,
                'base_salarial' => 3000.00,
                'salario_base' => 3000.00,
                'insalubridade' => 300.00,
                'periculosidade' => 600.00,
                'horas_extras' => 450.00,
                'adicional_noturno' => 250.00,
                'extras' => 200.00,
                'vale_transporte' => 180.00,
                'beneficios' => 450.00,
                'encargos_sociais' => 1440.00,
                'custo_total_mao_obra' => 6870.00,
                'percentual_encargos' => 48.00,
                'percentual_beneficios' => 15.00,
                'active' => true,
            ],
            [
                'tipo_contratacao' => 'Terceirizado',
                'cargo' => 'Segurança',
                'base_id' => $baseIds[1] ?? $baseIds[0],
                'base_salarial' => 2200.00,
                'salario_base' => 2200.00,
                'insalubridade' => 0.00,
                'periculosidade' => 440.00,
                'horas_extras' => 200.00,
                'adicional_noturno' => 400.00,
                'extras' => 100.00,
                'vale_transporte' => 180.00,
                'beneficios' => 350.00,
                'encargos_sociais' => 1056.00,
                'custo_total_mao_obra' => 4926.00,
                'percentual_encargos' => 48.00,
                'percentual_beneficios' => 15.91,
                'active' => true,
            ],
            [
                'tipo_contratacao' => 'CLT',
                'cargo' => 'Técnico em Manutenção',
                'base_id' => $baseIds[0] ?? 1,
                'base_salarial' => 3500.00,
                'salario_base' => 3500.00,
                'insalubridade' => 350.00,
                'periculosidade' => 700.00,
                'horas_extras' => 500.00,
                'adicional_noturno' => 200.00,
                'extras' => 250.00,
                'vale_transporte' => 180.00,
                'beneficios' => 550.00,
                'encargos_sociais' => 1680.00,
                'custo_total_mao_obra' => 7910.00,
                'percentual_encargos' => 48.00,
                'percentual_beneficios' => 15.71,
                'active' => true,
            ],
            [
                'tipo_contratacao' => 'CLT',
                'cargo' => 'Coordenador',
                'base_id' => $baseIds[1] ?? $baseIds[0],
                'base_salarial' => 6000.00,
                'salario_base' => 6000.00,
                'insalubridade' => 0.00,
                'periculosidade' => 0.00,
                'horas_extras' => 800.00,
                'adicional_noturno' => 0.00,
                'extras' => 400.00,
                'vale_transporte' => 180.00,
                'beneficios' => 900.00,
                'encargos_sociais' => 2880.00,
                'custo_total_mao_obra' => 11160.00,
                'percentual_encargos' => 48.00,
                'percentual_beneficios' => 15.00,
                'active' => true,
            ],
            [
                'tipo_contratacao' => 'CLT',
                'cargo' => 'Estagiário',
                'base_id' => $baseIds[0] ?? 1,
                'base_salarial' => 1000.00,
                'salario_base' => 1000.00,
                'insalubridade' => 0.00,
                'periculosidade' => 0.00,
                'horas_extras' => 0.00,
                'adicional_noturno' => 0.00,
                'extras' => 50.00,
                'vale_transporte' => 180.00,
                'beneficios' => 150.00,
                'encargos_sociais' => 120.00,
                'custo_total_mao_obra' => 1500.00,
                'percentual_encargos' => 12.00,
                'percentual_beneficios' => 15.00,
                'active' => false,
            ],
        ];

        foreach ($recursosHumanos as $recursoHumano) {
            RecursoHumano::firstOrCreate(
                [
                    'cargo' => $recursoHumano['cargo'],
                    'base_id' => $recursoHumano['base_id']
                ],
                $recursoHumano
            );
        }

        $this->command->info('Recursos Humanos criados com sucesso!');
    }
}
