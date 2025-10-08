<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Imposto;

class ImpostoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $impostos = [
            [
                'nome' => 'ICMS',
                'descricao' => 'Imposto sobre Circulação de Mercadorias e Serviços',
                'percentual' => 18.0000,
                'ativo' => true,
            ],
            [
                'nome' => 'IPI',
                'descricao' => 'Imposto sobre Produtos Industrializados',
                'percentual' => 10.0000,
                'ativo' => true,
            ],
            [
                'nome' => 'PIS',
                'descricao' => 'Programa de Integração Social',
                'percentual' => 1.6500,
                'ativo' => true,
            ],
            [
                'nome' => 'COFINS',
                'descricao' => 'Contribuição para o Financiamento da Seguridade Social',
                'percentual' => 7.6000,
                'ativo' => true,
            ],
            [
                'nome' => 'ISS',
                'descricao' => 'Imposto sobre Serviços de Qualquer Natureza',
                'percentual' => 5.0000,
                'ativo' => true,
            ],
            [
                'nome' => 'IRPJ',
                'descricao' => 'Imposto de Renda Pessoa Jurídica',
                'percentual' => 15.0000,
                'ativo' => true,
            ],
            [
                'nome' => 'CSLL',
                'descricao' => 'Contribuição Social sobre o Lucro Líquido',
                'percentual' => 9.0000,
                'ativo' => true,
            ],
            [
                'nome' => 'INSS',
                'descricao' => 'Instituto Nacional do Seguro Social',
                'percentual' => 20.0000,
                'ativo' => true,
            ],
            [
                'nome' => 'FGTS',
                'descricao' => 'Fundo de Garantia do Tempo de Serviço',
                'percentual' => 8.0000,
                'ativo' => true,
            ],
            [
                'nome' => 'IOF',
                'descricao' => 'Imposto sobre Operações Financeiras',
                'percentual' => 0.3800,
                'ativo' => false,
            ],
        ];

        foreach ($impostos as $imposto) {
            Imposto::firstOrCreate(
                ['nome' => $imposto['nome']],
                $imposto
            );
        }

        $this->command->info('Impostos criados com sucesso!');
    }
}
