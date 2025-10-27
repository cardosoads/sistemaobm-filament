<?php

namespace App\Filament\Resources\OrcamentoResource\Pages;

use App\Filament\Resources\OrcamentoResource;
use App\Models\OrcamentoPrestador;
use App\Models\OrcamentoAumentoKm;
use App\Models\OrcamentoProprioNovaRota;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrcamento extends CreateRecord
{
    protected static string $resource = OrcamentoResource::class;
    
    protected array $prestadorData = [];
    protected array $aumentoKmData = [];
    protected array $proprioNovaRotaData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Se for orçamento do tipo prestador, extrair dados específicos do prestador
        if (($data['tipo_orcamento'] ?? null) === 'prestador') {
            // Campos que pertencem à tabela orcamento_prestador
            $prestadorFields = [
                'fornecedor_omie_id',
                'valor_referencia', 
                'qtd_dias',
                'custo_fornecedor',
                'lucro_percentual',
                'valor_lucro',
                'impostos_percentual',
                'valor_impostos',
                'valor_total',
                'grupo_imposto_id'
            ];

            // Extrair dados do prestador
            $prestadorData = [];
            foreach ($prestadorFields as $field) {
                if (isset($data[$field])) {
                    $prestadorData[$field] = $data[$field];
                    unset($data[$field]); // Remover do array principal
                }
            }

            // Armazenar dados do prestador para usar no handleRecordCreation
            $this->prestadorData = $prestadorData;
        }
        
        // Se for orçamento do tipo aumento_km, extrair dados específicos do aumento de km
        if (($data['tipo_orcamento'] ?? null) === 'aumento_km') {
            // Campos que pertencem à tabela orcamento_aumento_km
            $aumentoKmFields = [
                'km_por_dia',
                'quantidade_dias_aumento',
                'combustivel_km_litro',
                'valor_combustivel',
                'hora_extra',
                'pedagio',
                'valor_total',
                'percentual_lucro',
                'valor_lucro',
                'percentual_impostos',
                'valor_impostos',
                'valor_final',
                'grupo_imposto_id'
            ];

            // Extrair dados do aumento de km
            $aumentoKmData = [];
            foreach ($aumentoKmFields as $field) {
                if (isset($data[$field])) {
                    $aumentoKmData[$field] = $data[$field];
                    unset($data[$field]); // Remover do array principal
                }
            }

            // Armazenar dados do aumento de km para usar no handleRecordCreation
            $this->aumentoKmData = $aumentoKmData;
        }
        
        // Se for orçamento do tipo proprio_nova_rota, extrair dados específicos do próprios nova rota
        if (($data['tipo_orcamento'] ?? null) === 'proprio_nova_rota') {
            // Campos que pertencem à tabela orcamento_proprio_nova_rota
            $proprioNovaRotaFields = [
                'incluir_funcionario',
                'incluir_frota',
                'incluir_fornecedor',
                'recurso_humano_id',
                'base_id',
                'valor_funcionario',
                'frota_id',
                'valor_aluguel_frota',
                'quantidade_dias',
                'valor_combustivel',
                'valor_pedagio',
                'fornecedor_omie_id',
                'fornecedor_nome',
                'fornecedor_referencia',
                'fornecedor_dias',
                'fornecedor_custo',
                'fornecedor_lucro',
                'fornecedor_impostos',
                'fornecedor_total',
                'valor_total_rotas',
                'valor_total_geral',
                'lucro_percentual',
                'valor_lucro',
                'impostos_percentual',
                'valor_impostos',
                'valor_final',
                'grupo_imposto_id'
            ];

            // Extrair dados do próprios nova rota
            $proprioNovaRotaData = [];
            foreach ($proprioNovaRotaFields as $field) {
                if (isset($data[$field])) {
                    $proprioNovaRotaData[$field] = $data[$field];
                    unset($data[$field]); // Remover do array principal
                }
            }
            
            // Mapear campos da seção "Valores" principal para os campos específicos do próprios nova rota
            if (isset($data['valor_total'])) {
                $proprioNovaRotaData['valor_total_geral'] = $data['valor_total'];
                unset($data['valor_total']);
            }
            if (isset($data['valor_impostos'])) {
                $proprioNovaRotaData['valor_impostos'] = $data['valor_impostos'];
                unset($data['valor_impostos']);
            }
            if (isset($data['valor_final'])) {
                $proprioNovaRotaData['valor_final'] = $data['valor_final'];
                unset($data['valor_final']);
            }
            
            // Mapear o campo grupo_imposto_id_rota para grupo_imposto_id
            if (isset($data['grupo_imposto_id_rota'])) {
                $proprioNovaRotaData['grupo_imposto_id'] = $data['grupo_imposto_id_rota'];
                unset($data['grupo_imposto_id_rota']);
            }
            
            // Mapear os campos de percentuais da rota para os nomes corretos da tabela
            if (isset($data['lucro_percentual_rota'])) {
                $proprioNovaRotaData['lucro_percentual'] = $data['lucro_percentual_rota'];
                unset($data['lucro_percentual_rota']);
            }
            if (isset($data['impostos_percentual_rota'])) {
                $proprioNovaRotaData['impostos_percentual'] = $data['impostos_percentual_rota'];
                unset($data['impostos_percentual_rota']);
            }

            // Armazenar dados do próprios nova rota para usar no handleRecordCreation
            $this->proprioNovaRotaData = $proprioNovaRotaData;
        }

        // Garantir valores padrão para campos obrigatórios
        $data['valor_funcionario'] = $data['valor_funcionario'] ?? 0;
        $data['valor_aluguel_frota'] = $data['valor_aluguel_frota'] ?? 0;
        $data['fornecedor_referencia'] = $data['fornecedor_referencia'] ?? 0;
        $data['fornecedor_dias'] = $data['fornecedor_dias'] ?? 1;
        $data['lucro_percentual_rota'] = $data['lucro_percentual_rota'] ?? 0;
        $data['impostos_percentual_rota'] = $data['impostos_percentual_rota'] ?? 0;

        // Remover campos específicos baseado no tipo de orçamento
        if ($data['tipo_orcamento'] === 'prestador') {
            $data['valor_funcionario'] = 0;
            $data['valor_aluguel_frota'] = 0;
            $data['fornecedor_omie_id_rota'] = null;
            $data['fornecedor_referencia'] = 0;
            $data['fornecedor_dias'] = 1;
            $data['lucro_percentual_rota'] = 0;
            $data['impostos_percentual_rota'] = 0;
        } elseif ($data['tipo_orcamento'] === 'aumento_km') {
            $data['valor_funcionario'] = 0;
            $data['valor_aluguel_frota'] = 0;
            $data['fornecedor_omie_id_rota'] = null;
            $data['fornecedor_referencia'] = 0;
            $data['fornecedor_dias'] = 1;
            $data['lucro_percentual_rota'] = 0;
            $data['impostos_percentual_rota'] = 0;
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Criar o orçamento primeiro
        $record = static::getModel()::create($data);
        
        // Se existem dados do prestador para salvar
        if (isset($this->prestadorData) && !empty($this->prestadorData)) {
            // Criar novo registro de prestador
            $this->prestadorData['orcamento_id'] = $record->id;
            OrcamentoPrestador::create($this->prestadorData);
        }
        
        // Se existem dados do aumento de km para salvar
        if (isset($this->aumentoKmData) && !empty($this->aumentoKmData)) {
            // Criar novo registro de aumento de km
            $this->aumentoKmData['orcamento_id'] = $record->id;
            OrcamentoAumentoKm::create($this->aumentoKmData);
        }
        
        // Se existem dados do próprios nova rota para salvar
        if (isset($this->proprioNovaRotaData) && !empty($this->proprioNovaRotaData)) {
            // Criar novo registro de próprios nova rota
            $this->proprioNovaRotaData['orcamento_id'] = $record->id;
            OrcamentoProprioNovaRota::create($this->proprioNovaRotaData);
        }

        return $record;
    }
}