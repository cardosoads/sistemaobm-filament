<?php

namespace App\Services;

use App\Models\OrcamentoPrestador;
use App\Models\OrcamentoAumentoKm;
use App\Models\OrcamentoProprioNovaRota;
use App\Models\GrupoImposto;

class CalculatorService
{
    public function calcularPrestador(OrcamentoPrestador $prestador): void
    {
        // Calcular dias baseado na frequência
        $dias = $this->calcularDiasPorFrequencia($prestador);
        
        // Cálculo do custo do fornecedor
        $custoFornecedor = $prestador->valor_referencia * $dias;
        
        // Cálculo do lucro
        $valorLucro = $custoFornecedor * ($prestador->lucro_percentual / 100);
        
        // Cálculo dos impostos (sobre custo + lucro)
        $baseImpostos = $custoFornecedor + $valorLucro;
        $valorImpostos = $baseImpostos * ($prestador->impostos_percentual / 100);
        
        // Valor total
        $valorTotal = $baseImpostos + $valorImpostos;
        
        // Atualizar valores
        $prestador->update([
            'custo_fornecedor' => $custoFornecedor,
            'valor_lucro' => $valorLucro,
            'valor_impostos' => $valorImpostos,
            'valor_total' => $valorTotal,
        ]);
    }

    public function calcularAumentoKm(OrcamentoAumentoKm $aumentoKm): void
    {
        // Cálculo do valor total base
        $valorTotal = 
            ($aumentoKm->km_extra * $aumentoKm->valor_km_extra) +
            ($aumentoKm->litros_combustivel * $aumentoKm->valor_combustivel) +
            ($aumentoKm->horas_extras * $aumentoKm->valor_hora_extra) +
            $aumentoKm->valor_pedagio;
        
        // Cálculo do lucro
        $valorLucro = $valorTotal * ($aumentoKm->lucro_percentual / 100);
        
        // Cálculo dos impostos (sobre total + lucro)
        $baseImpostos = $valorTotal + $valorLucro;
        $valorImpostos = $baseImpostos * ($aumentoKm->impostos_percentual / 100);
        
        // Valor final
        $valorFinal = $baseImpostos + $valorImpostos;
        
        // Atualizar valores
        $aumentoKm->update([
            'valor_total' => $valorTotal,
            'valor_lucro' => $valorLucro,
            'valor_impostos' => $valorImpostos,
            'valor_final' => $valorFinal,
        ]);
    }

    public function calcularProprioNovaRota(OrcamentoProprioNovaRota $proprioNovaRota): void
    {
        // Cálculo do custo do fornecedor
        $custoFornecedor = $proprioNovaRota->fornecedor_referencia * $proprioNovaRota->fornecedor_dias;
        
        // Cálculo do lucro do fornecedor
        $fornecedorLucro = $custoFornecedor * ($proprioNovaRota->lucro_percentual / 100);
        
        // Cálculo dos impostos do fornecedor
        $baseImpostosFornecedor = $custoFornecedor + $fornecedorLucro;
        $fornecedorImpostos = $baseImpostosFornecedor * ($proprioNovaRota->impostos_percentual / 100);
        
        // Valor total do fornecedor
        $fornecedorTotal = $baseImpostosFornecedor + $fornecedorImpostos;
        
        // Valor total geral (funcionário + frota + fornecedor)
        $valorTotalGeral = 
            $proprioNovaRota->valor_funcionario +
            $proprioNovaRota->valor_aluguel_frota +
            $fornecedorTotal;
        
        // Cálculo do lucro geral (sobre o total)
        $valorLucro = $valorTotalGeral * ($proprioNovaRota->lucro_percentual / 100);
        
        // Cálculo dos impostos gerais
        $baseImpostos = $valorTotalGeral + $valorLucro;
        $valorImpostos = $baseImpostos * ($proprioNovaRota->impostos_percentual / 100);
        
        // Valor final
        $valorFinal = $baseImpostos + $valorImpostos;
        
        // Atualizar valores
        $proprioNovaRota->update([
            'fornecedor_custo' => $custoFornecedor,
            'fornecedor_lucro' => $fornecedorLucro,
            'fornecedor_impostos' => $fornecedorImpostos,
            'fornecedor_total' => $fornecedorTotal,
            'valor_total_geral' => $valorTotalGeral,
            'valor_lucro' => $valorLucro,
            'valor_impostos' => $valorImpostos,
            'valor_final' => $valorFinal,
        ]);
    }

    protected function calcularDiasPorFrequencia(OrcamentoPrestador $prestador): int
    {
        if (!$prestador->orcamento) {
            return $prestador->qtd_dias ?? 1;
        }

        return match($prestador->orcamento->frequencia_atendimento) {
            'diario' => $prestador->qtd_dias * 30,
            'semanal' => $prestador->qtd_dias * 4,
            'quinzenal' => $prestador->qtd_dias * 2,
            'mensal' => $prestador->qtd_dias,
            default => $prestador->qtd_dias ?? 1,
        };
    }

    public function calcularImpostos(float $valor, GrupoImposto $grupoImposto): float
    {
        return $valor * ($grupoImposto->percentual / 100);
    }

    public function calcularLucro(float $valor, float $percentualLucro): float
    {
        return $valor * ($percentualLucro / 100);
    }

    public function calcularValorComImpostos(float $valorBase, float $percentualLucro, float $percentualImpostos): float
    {
        $lucro = $this->calcularLucro($valorBase, $percentualLucro);
        $baseImpostos = $valorBase + $lucro;
        $impostos = $this->calcularImpostos($baseImpostos, new GrupoImposto(['percentual' => $percentualImpostos]));
        
        return $baseImpostos + $impostos;
    }

    public function simularOrcamentoPrestador(float $valorReferencia, int $dias, float $percentualLucro, float $percentualImpostos): array
    {
        $custoFornecedor = $valorReferencia * $dias;
        $valorLucro = $this->calcularLucro($custoFornecedor, $percentualLucro);
        $baseImpostos = $custoFornecedor + $valorLucro;
        $valorImpostos = $this->calcularImpostos($baseImpostos, new GrupoImposto(['percentual' => $percentualImpostos]));
        $valorTotal = $baseImpostos + $valorImpostos;
        
        return [
            'custo_fornecedor' => $custoFornecedor,
            'valor_lucro' => $valorLucro,
            'valor_impostos' => $valorImpostos,
            'valor_total' => $valorTotal,
        ];
    }

    public function simularOrcamentoAumentoKm(float $kmExtra, float $valorKmExtra, float $litrosCombustivel, float $valorCombustivel, float $horasExtras, float $valorHoraExtra, float $valorPedagio, float $percentualLucro, float $percentualImpostos): array
    {
        $valorTotal = ($kmExtra * $valorKmExtra) + ($litrosCombustivel * $valorCombustivel) + ($horasExtras * $valorHoraExtra) + $valorPedagio;
        $valorLucro = $this->calcularLucro($valorTotal, $percentualLucro);
        $baseImpostos = $valorTotal + $valorLucro;
        $valorImpostos = $this->calcularImpostos($baseImpostos, new GrupoImposto(['percentual' => $percentualImpostos]));
        $valorFinal = $baseImpostos + $valorImpostos;
        
        return [
            'valor_total' => $valorTotal,
            'valor_lucro' => $valorLucro,
            'valor_impostos' => $valorImpostos,
            'valor_final' => $valorFinal,
        ];
    }
}