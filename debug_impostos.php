<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Configuração do banco de dados
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'sistemaobm',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== DEBUG IMPOSTOS - VERIFICAÇÃO APÓS CORREÇÕES ===\n\n";

// Buscar especificamente o orçamento ID 14 que estávamos testando
echo "=== ORÇAMENTO ID 14 - VERIFICAÇÃO FINAL ===\n";
$orcamento = Capsule::table('orcamentos')
    ->where('id', 14)
    ->where('tipo', 'proprio_nova_rota')
    ->first();

if ($orcamento) {
    echo "Tipo: {$orcamento->tipo}\n";
    echo "Valor Impostos (Orçamento): {$orcamento->valor_impostos}\n";
    
    // Buscar dados específicos da tabela orcamento_proprio_nova_rota
    $proprioNovaRota = Capsule::table('orcamento_proprio_nova_rota')
        ->where('orcamento_id', $orcamento->id)
        ->first();
    
    if ($proprioNovaRota) {
        echo "=== Dados Próprio Nova Rota ===\n";
        echo "Valor Total Geral: {$proprioNovaRota->valor_total_geral}\n";
        echo "Valor Impostos: {$proprioNovaRota->valor_impostos}\n";
        echo "Impostos Percentual: {$proprioNovaRota->impostos_percentual}\n";
        echo "Grupo Imposto ID: " . ($proprioNovaRota->grupo_imposto_id ?? 'NULL') . "\n";
        echo "Valor Lucro: {$proprioNovaRota->valor_lucro}\n";
        echo "Lucro Percentual: {$proprioNovaRota->lucro_percentual}\n";
        echo "Valor Final: {$proprioNovaRota->valor_final}\n";
        
        // Se há grupo de imposto, buscar detalhes
        if ($proprioNovaRota->grupo_imposto_id) {
            $grupoImposto = Capsule::table('grupo_impostos')
                ->where('id', $proprioNovaRota->grupo_imposto_id)
                ->first();
            if ($grupoImposto) {
                echo "Grupo Imposto Nome: {$grupoImposto->nome}\n";
                echo "Grupo Imposto Percentual: {$grupoImposto->percentual_total}%\n";
                
                // Verificar se os cálculos estão corretos
                $valorTotalGeral = floatval($proprioNovaRota->valor_total_geral);
                $percentualImposto = floatval($grupoImposto->percentual_total);
                $valorImpostosCalculado = ($valorTotalGeral * $percentualImposto) / 100;
                $valorImpostosSalvo = floatval($proprioNovaRota->valor_impostos);
                
                echo "\n=== VERIFICAÇÃO DE CÁLCULOS ===\n";
                echo "Valor Total Geral: R$ " . number_format($valorTotalGeral, 2, ',', '.') . "\n";
                echo "Percentual Imposto: {$percentualImposto}%\n";
                echo "Valor Impostos Calculado: R$ " . number_format($valorImpostosCalculado, 2, ',', '.') . "\n";
                echo "Valor Impostos Salvo: R$ " . number_format($valorImpostosSalvo, 2, ',', '.') . "\n";
                echo "Diferença: R$ " . number_format(abs($valorImpostosCalculado - $valorImpostosSalvo), 2, ',', '.') . "\n";
                
                if (abs($valorImpostosCalculado - $valorImpostosSalvo) < 0.01) {
                    echo "✅ CÁLCULO CORRETO!\n";
                } else {
                    echo "❌ CÁLCULO INCORRETO!\n";
                }
            }
        } else {
            echo "❌ GRUPO DE IMPOSTO NÃO DEFINIDO!\n";
        }
    } else {
        echo "❌ DADOS PRÓPRIO NOVA ROTA NÃO ENCONTRADOS!\n";
    }
} else {
    echo "❌ ORÇAMENTO ID 14 NÃO ENCONTRADO!\n";
}

echo "\n=== TODOS OS ORÇAMENTOS TIPO 'proprio_nova_rota' ===\n";
$orcamentos = Capsule::table('orcamentos')
    ->where('tipo', 'proprio_nova_rota')
    ->get();

foreach ($orcamentos as $orcamento) {
    echo "ID: {$orcamento->id} | Valor Impostos: {$orcamento->valor_impostos}\n";
}