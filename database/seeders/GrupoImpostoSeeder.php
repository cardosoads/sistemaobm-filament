<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GrupoImposto;
use App\Models\Imposto;

class GrupoImpostoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gruposImpostos = [
            [
                'nome' => 'Impostos Federais',
                'descricao' => 'Grupo de impostos federais obrigatórios',
                'ativo' => true,
                'impostos' => ['IPI', 'PIS', 'COFINS', 'IRPJ', 'CSLL']
            ],
            [
                'nome' => 'Impostos Estaduais',
                'descricao' => 'Grupo de impostos estaduais',
                'ativo' => true,
                'impostos' => ['ICMS']
            ],
            [
                'nome' => 'Impostos Municipais',
                'descricao' => 'Grupo de impostos municipais',
                'ativo' => true,
                'impostos' => ['ISS']
            ],
            [
                'nome' => 'Encargos Trabalhistas',
                'descricao' => 'Grupo de encargos trabalhistas obrigatórios',
                'ativo' => true,
                'impostos' => ['INSS', 'FGTS']
            ],
            [
                'nome' => 'Simples Nacional',
                'descricao' => 'Grupo para empresas do Simples Nacional',
                'ativo' => true,
                'impostos' => ['ICMS', 'PIS', 'COFINS', 'IRPJ', 'CSLL']
            ],
            [
                'nome' => 'Lucro Presumido',
                'descricao' => 'Grupo para empresas do Lucro Presumido',
                'ativo' => true,
                'impostos' => ['ICMS', 'IPI', 'PIS', 'COFINS', 'IRPJ', 'CSLL']
            ],
            [
                'nome' => 'Lucro Real',
                'descricao' => 'Grupo para empresas do Lucro Real',
                'ativo' => true,
                'impostos' => ['ICMS', 'IPI', 'PIS', 'COFINS', 'IRPJ', 'CSLL']
            ],
            [
                'nome' => 'Prestação de Serviços',
                'descricao' => 'Grupo específico para prestação de serviços',
                'ativo' => true,
                'impostos' => ['ISS', 'PIS', 'COFINS', 'IRPJ', 'CSLL']
            ],
            [
                'nome' => 'Operações Financeiras',
                'descricao' => 'Grupo para operações financeiras específicas',
                'ativo' => false,
                'impostos' => ['IOF']
            ],
            [
                'nome' => 'Grupo Especial',
                'descricao' => 'Grupo especial para casos específicos',
                'ativo' => false,
                'impostos' => ['ICMS', 'ISS', 'IOF']
            ],
        ];

        foreach ($gruposImpostos as $grupoData) {
            $impostos = $grupoData['impostos'];
            unset($grupoData['impostos']);

            $grupo = GrupoImposto::firstOrCreate(
                ['nome' => $grupoData['nome']],
                $grupoData
            );

            // Associar impostos ao grupo
            $impostosIds = Imposto::whereIn('nome', $impostos)->pluck('id');
            $grupo->impostos()->sync($impostosIds);
        }

        $this->command->info('Grupos de Impostos criados com sucesso!');
    }
}
