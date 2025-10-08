<?php

namespace App\Services;

use App\Models\Orcamento;
use App\Models\OrcamentoPrestador;
use App\Models\OrcamentoAumentoKm;
use App\Models\OrcamentoProprioNovaRota;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrcamentoService
{
    protected CalculatorService $calculatorService;
    protected OmieService $omieService;

    public function __construct(CalculatorService $calculatorService, OmieService $omieService)
    {
        $this->calculatorService = $calculatorService;
        $this->omieService = $omieService;
    }

    public function criarOrcamento(array $dados): Orcamento
    {
        return DB::transaction(function () use ($dados) {
            $orcamento = Orcamento::create($dados);
            
            Log::info('Orçamento criado', [
                'id' => $orcamento->id,
                'numero' => $orcamento->numero_orcamento,
                'tipo' => $orcamento->tipo_orcamento,
            ]);
            
            return $orcamento;
        });
    }

    public function atualizarOrcamento(Orcamento $orcamento, array $dados): Orcamento
    {
        return DB::transaction(function () use ($orcamento, $dados) {
            $orcamento->update($dados);
            
            // Recalcular valores baseado nos itens relacionados
            $this->recalcularValoresOrcamento($orcamento);
            
            Log::info('Orçamento atualizado', [
                'id' => $orcamento->id,
                'numero' => $orcamento->numero_orcamento,
            ]);
            
            return $orcamento;
        });
    }

    public function excluirOrcamento(Orcamento $orcamento): bool
    {
        return DB::transaction(function () use ($orcamento) {
            // Excluir todos os itens relacionados primeiro
            $orcamento->prestadores()->delete();
            $orcamento->aumentosKm()->delete();
            $orcamento->propriosNovaRota()->delete();
            
            $resultado = $orcamento->delete();
            
            Log::info('Orçamento excluído', [
                'id' => $orcamento->id,
                'numero' => $orcamento->numero_orcamento,
            ]);
            
            return $resultado;
        });
    }

    public function recalcularValoresOrcamento(Orcamento $orcamento): void
    {
        switch ($orcamento->tipo_orcamento) {
            case 'prestador':
                $this->recalcularValoresPrestador($orcamento);
                break;
            case 'aumento_km':
                $this->recalcularValoresAumentoKm($orcamento);
                break;
            case 'proprio_nova_rota':
                $this->recalcularValoresProprioNovaRota($orcamento);
                break;
        }
    }

    protected function recalcularValoresPrestador(Orcamento $orcamento): void
    {
        $valorTotal = 0;
        $valorImpostos = 0;
        
        foreach ($orcamento->prestadores as $prestador) {
            $this->calculatorService->calcularPrestador($prestador);
            $valorTotal += $prestador->valor_total;
            $valorImpostos += $prestador->valor_impostos;
        }
        
        $orcamento->update([
            'valor_total' => $valorTotal,
            'valor_impostos' => $valorImpostos,
            'valor_final' => $valorTotal,
        ]);
    }

    protected function recalcularValoresAumentoKm(Orcamento $orcamento): void
    {
        $valorTotal = 0;
        $valorImpostos = 0;
        
        foreach ($orcamento->aumentosKm as $aumentoKm) {
            $this->calculatorService->calcularAumentoKm($aumentoKm);
            $valorTotal += $aumentoKm->valor_final;
            $valorImpostos += $aumentoKm->valor_impostos;
        }
        
        $orcamento->update([
            'valor_total' => $valorTotal,
            'valor_impostos' => $valorImpostos,
            'valor_final' => $valorTotal,
        ]);
    }

    protected function recalcularValoresProprioNovaRota(Orcamento $orcamento): void
    {
        $valorTotal = 0;
        $valorImpostos = 0;
        
        foreach ($orcamento->propriosNovaRota as $proprioNovaRota) {
            $this->calculatorService->calcularProprioNovaRota($proprioNovaRota);
            $valorTotal += $proprioNovaRota->valor_final;
            $valorImpostos += $proprioNovaRota->valor_impostos;
        }
        
        $orcamento->update([
            'valor_total' => $valorTotal,
            'valor_impostos' => $valorImpostos,
            'valor_final' => $valorTotal,
        ]);
    }

    public function aprovarOrcamento(Orcamento $orcamento): bool
    {
        if ($orcamento->status !== 'pendente') {
            throw new \Exception('Orçamento já está ' . $orcamento->status);
        }
        
        return DB::transaction(function () use ($orcamento) {
            $orcamento->update(['status' => 'aprovado']);
            
            Log::info('Orçamento aprovado', [
                'id' => $orcamento->id,
                'numero' => $orcamento->numero_orcamento,
            ]);
            
            return true;
        });
    }

    public function rejeitarOrcamento(Orcamento $orcamento, string $motivo = null): bool
    {
        if ($orcamento->status !== 'pendente') {
            throw new \Exception('Orçamento já está ' . $orcamento->status);
        }
        
        return DB::transaction(function () use ($orcamento, $motivo) {
            $dados = ['status' => 'rejeitado'];
            if ($motivo) {
                $dados['observacoes'] = $orcamento->observacoes . "\nMotivo da rejeição: " . $motivo;
            }
            
            $orcamento->update($dados);
            
            Log::info('Orçamento rejeitado', [
                'id' => $orcamento->id,
                'numero' => $orcamento->numero_orcamento,
                'motivo' => $motivo,
            ]);
            
            return true;
        });
    }

    public function duplicarOrcamento(Orcamento $orcamento): Orcamento
    {
        return DB::transaction(function () use ($orcamento) {
            // Criar cópia do orçamento
            $novoOrcamento = $orcamento->replicate();
            $novoOrcamento->numero_orcamento = null; // Gerar novo número
            $novoOrcamento->status = 'pendente';
            $novoOrcamento->save();
            
            // Duplicar itens baseado no tipo
            switch ($orcamento->tipo_orcamento) {
                case 'prestador':
                    foreach ($orcamento->prestadores as $prestador) {
                        $novoPrestador = $prestador->replicate();
                        $novoPrestador->orcamento_id = $novoOrcamento->id;
                        $novoPrestador->save();
                    }
                    break;
                case 'aumento_km':
                    foreach ($orcamento->aumentosKm as $aumentoKm) {
                        $novoAumentoKm = $aumentoKm->replicate();
                        $novoAumentoKm->orcamento_id = $novoOrcamento->id;
                        $novoAumentoKm->save();
                    }
                    break;
                case 'proprio_nova_rota':
                    foreach ($orcamento->propriosNovaRota as $proprioNovaRota) {
                        $novoProprioNovaRota = $proprioNovaRota->replicate();
                        $novoProprioNovaRota->orcamento_id = $novoOrcamento->id;
                        $novoProprioNovaRota->save();
                    }
                    break;
            }
            
            // Recalcular valores do novo orçamento
            $this->recalcularValoresOrcamento($novoOrcamento);
            
            Log::info('Orçamento duplicado', [
                'id_original' => $orcamento->id,
                'id_novo' => $novoOrcamento->id,
                'numero_original' => $orcamento->numero_orcamento,
                'numero_novo' => $novoOrcamento->numero_orcamento,
            ]);
            
            return $novoOrcamento;
        });
    }

    public function buscarClienteOmie(int $clienteId): ?array
    {
        return $this->omieService->buscarCliente($clienteId);
    }

    public function buscarFornecedorOmie(int $fornecedorId): ?array
    {
        return $this->omieService->buscarFornecedor($fornecedorId);
    }
}