<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Testar dados do prestador
$prestador = \App\Models\OrcamentoPrestador::first();

if ($prestador) {
    echo "Dados do prestador:\n";
    echo "ID: " . $prestador->id . "\n";
    echo "Valor Referência: " . $prestador->valor_referencia . "\n";
    echo "Qtd Dias: " . $prestador->qtd_dias . "\n";
    echo "Lucro %: " . $prestador->lucro_percentual . "\n";
    echo "Impostos %: " . $prestador->impostos_percentual . "\n";
    echo "Fornecedor OMIE ID: " . $prestador->fornecedor_omie_id . "\n";
    echo "Grupo Imposto ID: " . $prestador->grupo_imposto_id . "\n";
    
    // Testar relacionamentos
    echo "\nTestando relacionamentos:\n";
    
    try {
        $fornecedor = $prestador->fornecedor;
        echo "Fornecedor: " . ($fornecedor ? $fornecedor->razao_social : 'NULL') . "\n";
    } catch (Exception $e) {
        echo "Erro no relacionamento fornecedor: " . $e->getMessage() . "\n";
    }
    
    try {
        $grupoImposto = $prestador->grupoImposto;
        echo "Grupo Imposto: " . ($grupoImposto ? $grupoImposto->nome : 'NULL') . "\n";
    } catch (Exception $e) {
        echo "Erro no relacionamento grupoImposto: " . $e->getMessage() . "\n";
    }
} else {
    echo "Nenhum prestador encontrado\n";
}