<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Orcamento;
use App\Models\Obm;

$orcamento = Orcamento::where('numero_orcamento', 'ORC2025000011')->first();

if ($orcamento) {
    echo "=== ESTADO ATUAL DO ORÇAMENTO ===\n";
    echo "ID: " . $orcamento->id . "\n";
    echo "Número: " . $orcamento->numero_orcamento . "\n";
    echo "Tipo: " . $orcamento->tipo_orcamento . "\n";
    echo "Incluir Funcionário: " . ($orcamento->incluir_funcionario ? 'true' : 'false') . "\n";
    echo "Incluir Frota: " . ($orcamento->incluir_frota ? 'true' : 'false') . "\n";
    echo "Incluir Prestador: " . ($orcamento->incluir_prestador ? 'true' : 'false') . "\n";
    
    // Verificar OBMs existentes
    $obms = Obm::where('orcamento_id', $orcamento->id)->get();
    echo "\n=== OBMs EXISTENTES ===\n";
    echo "Total de OBMs: " . $obms->count() . "\n";
    
    foreach ($obms as $obm) {
        echo "OBM ID: {$obm->id}, Colaborador: " . ($obm->colaborador_id ?? 'null') . ", Frota: " . ($obm->frota_id ?? 'null') . "\n";
    }
    
    echo "\n=== ANÁLISE DA LÓGICA ===\n";
    echo "Para tipo 'proprio_nova_rota':\n";
    echo "- Campo Colaborador deve aparecer: " . ($orcamento->incluir_funcionario ? 'SIM' : 'NÃO') . "\n";
    echo "- Campo Frota deve aparecer: " . ($orcamento->incluir_frota ? 'SIM' : 'NÃO') . "\n";
    echo "- Campo Prestador deve aparecer: " . ($orcamento->incluir_prestador ? 'SIM' : 'NÃO') . "\n";
    
} else {
    echo "Orçamento não encontrado\n";
}