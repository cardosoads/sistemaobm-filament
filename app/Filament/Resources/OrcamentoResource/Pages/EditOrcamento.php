<?php

namespace App\Filament\Resources\OrcamentoResource\Pages;

use App\Filament\Resources\OrcamentoResource;
use App\Models\OrcamentoPrestador;
use App\Models\OrcamentoAumentoKm;
use App\Models\OrcamentoProprioNovaRota;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrcamento extends EditRecord
{
    protected static string $resource = OrcamentoResource::class;
    
    protected array $prestadorData = [];
    protected array $aumentoKmData = [];
    protected array $proprioNovaRotaData = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Se for orçamento do tipo prestador, carregar dados do prestador
        if (($data['tipo_orcamento'] ?? null) === 'prestador') {
            $prestador = OrcamentoPrestador::where('orcamento_id', $this->record->id)->first();
            
            if ($prestador) {
                // Adicionar dados do prestador ao formulário
                $data = array_merge($data, $prestador->toArray());
            }
        }
        
        // Se for orçamento do tipo aumento_km, carregar dados do aumento de km
        if (($data['tipo_orcamento'] ?? null) === 'aumento_km') {
            $aumentoKm = OrcamentoAumentoKm::where('orcamento_id', $this->record->id)->first();
            
            if ($aumentoKm) {
                // Adicionar dados do aumento de km ao formulário
                $data = array_merge($data, $aumentoKm->toArray());
            }
        }
        
        // Se for orçamento do tipo proprio_nova_rota, carregar dados do próprios nova rota
        if (($data['tipo_orcamento'] ?? null) === 'proprio_nova_rota') {
            $proprioNovaRota = OrcamentoProprioNovaRota::where('orcamento_id', $this->record->id)->first();
            
            if ($proprioNovaRota) {
                // Adicionar dados do próprios nova rota ao formulário
                $proprioNovaRotaData = $proprioNovaRota->toArray();
                
                // Mapear os campos específicos para a seção "Valores" principal
                if (isset($proprioNovaRotaData['valor_total_geral'])) {
                    $data['valor_total'] = $proprioNovaRotaData['valor_total_geral'];
                }
                if (isset($proprioNovaRotaData['valor_impostos'])) {
                    $data['valor_impostos'] = $proprioNovaRotaData['valor_impostos'];
                }
                if (isset($proprioNovaRotaData['valor_final'])) {
                    $data['valor_final'] = $proprioNovaRotaData['valor_final'];
                }
                
                // Mapear o campo grupo_imposto_id para grupo_imposto_id_rota
                if (isset($proprioNovaRotaData['grupo_imposto_id'])) {
                    $data['grupo_imposto_id_rota'] = $proprioNovaRotaData['grupo_imposto_id'];
                }
                
                // Mapear os campos de percentuais da tabela para os nomes do formulário
                if (isset($proprioNovaRotaData['lucro_percentual'])) {
                    $data['lucro_percentual_rota'] = $proprioNovaRotaData['lucro_percentual'];
                }
                if (isset($proprioNovaRotaData['impostos_percentual'])) {
                    $data['impostos_percentual_rota'] = $proprioNovaRotaData['impostos_percentual'];
                }
                
                // Adicionar todos os outros dados do próprios nova rota
                $data = array_merge($data, $proprioNovaRotaData);
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

            // Armazenar dados do prestador para usar no handleRecordUpdate
            $this->prestadorData = $prestadorData;
            
            // IMPORTANTE: Manter os campos que pertencem à tabela principal 'orcamentos'
            // Os campos cliente_omie_id, frequencia_atendimento e valor_final devem permanecer
            // no array $data para serem salvos na tabela principal
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

            // Armazenar dados do aumento de km para usar no handleRecordUpdate
            $this->aumentoKmData = $aumentoKmData;
        }
        
        // Se for orçamento do tipo proprio_nova_rota, extrair dados específicos do próprios nova rota
        if (($data['tipo_orcamento'] ?? null) === 'proprio_nova_rota') {
            // Garantir que fornecedor_referencia não seja null quando incluir_prestador estiver marcado
            if (($data['incluir_prestador'] ?? false) && ($data['fornecedor_referencia'] ?? null) === null) {
                $data['fornecedor_referencia'] = 0;
            }
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
                    
                    // Não remover campos que também pertencem à tabela orcamentos
                    $camposCompartilhados = [
                        'incluir_funcionario',
                        'incluir_frota', 
                        'incluir_prestador',
                        'valor_funcionario',
                        'frota_id',
                        'fornecedor_omie_id',
                        'fornecedor_referencia',
                        'fornecedor_dias',
                        'lucro_percentual_rota',
                        'impostos_percentual_rota'
                    ];
                    
                    if (!in_array($field, $camposCompartilhados)) {
                        unset($data[$field]); // Remover do array principal apenas se não for compartilhado
                    }
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

            // Armazenar dados do próprios nova rota para usar no handleRecordUpdate
            $this->proprioNovaRotaData = $proprioNovaRotaData;
        }

        return $data;
    }

    protected function handleRecordUpdate($record, array $data): \Illuminate\Database\Eloquent\Model
    {
        \Log::info('EditOrcamento::handleRecordUpdate - Iniciando', [
            'record_id' => $record->id,
            'data' => $data,
            'tipo_orcamento' => $record->tipo_orcamento
        ]);
        
        // Chamar o método pai para salvar o registro principal
        $record = parent::handleRecordUpdate($record, $data);
        
        // Se existem dados do prestador para salvar
        if (isset($this->prestadorData) && !empty($this->prestadorData)) {
            // Verificar se já existe um registro de prestador para este orçamento
            $prestador = OrcamentoPrestador::where('orcamento_id', $record->id)->first();
            
            if ($prestador) {
                // Atualizar registro existente
                $prestador->update($this->prestadorData);
            } else {
                // Criar novo registro
                $this->prestadorData['orcamento_id'] = $record->id;
                OrcamentoPrestador::create($this->prestadorData);
            }
        }
        
        // Se existem dados do aumento de km para salvar
        if (isset($this->aumentoKmData) && !empty($this->aumentoKmData)) {
            // Verificar se já existe um registro de aumento de km para este orçamento
            $aumentoKm = OrcamentoAumentoKm::where('orcamento_id', $record->id)->first();
            
            if ($aumentoKm) {
                // Atualizar registro existente
                $aumentoKm->update($this->aumentoKmData);
            } else {
                // Criar novo registro
                $this->aumentoKmData['orcamento_id'] = $record->id;
                OrcamentoAumentoKm::create($this->aumentoKmData);
            }
        }
        
        // Se existem dados do próprios nova rota para salvar
        if (isset($this->proprioNovaRotaData) && !empty($this->proprioNovaRotaData)) {
            \Log::info('EditOrcamento::handleRecordUpdate - Processando proprioNovaRotaData', [
                'proprioNovaRotaData' => $this->proprioNovaRotaData
            ]);
            
            // Verificar se já existe um registro de próprios nova rota para este orçamento
            $proprioNovaRota = OrcamentoProprioNovaRota::where('orcamento_id', $record->id)->first();
            
            if ($proprioNovaRota) {
                // Atualizar registro existente
                \Log::info('EditOrcamento::handleRecordUpdate - Atualizando registro existente');
                $proprioNovaRota->update($this->proprioNovaRotaData);
            } else {
                // Criar novo registro
                \Log::info('EditOrcamento::handleRecordUpdate - Criando novo registro');
                $this->proprioNovaRotaData['orcamento_id'] = $record->id;
                $proprioNovaRota = OrcamentoProprioNovaRota::create($this->proprioNovaRotaData);
            }
            
            \Log::info('EditOrcamento::handleRecordUpdate - Dados após salvar proprioNovaRota', [
                'valor_total_geral' => $proprioNovaRota->valor_total_geral,
                'valor_impostos' => $proprioNovaRota->valor_impostos,
                'valor_final' => $proprioNovaRota->valor_final
            ]);
            
            // Atualizar os valores totais no orçamento principal
            $updateData = [
                'valor_total' => $proprioNovaRota->valor_total_geral ?? 0,
                'valor_impostos' => $proprioNovaRota->valor_impostos ?? 0,
                'valor_final' => $proprioNovaRota->valor_final ?? 0,
            ];
            
            \Log::info('EditOrcamento::handleRecordUpdate - Atualizando orçamento principal', [
                'updateData' => $updateData
            ]);
            
            $record->update($updateData);
        } else {
            \Log::info('EditOrcamento::handleRecordUpdate - Nenhum dado de proprioNovaRota para processar');
        }

        return $record;
    }

    public function getRelationManagers(): array
    {
        $relationManagers = [];
        
        $tipoOrcamento = $this->record->tipo_orcamento ?? null;
        
        switch ($tipoOrcamento) {
            case 'prestador':
                // Seção Prestadores removida
                break;
            case 'aumento_km':
                // Removido AumentosKmRelationManager para não exibir na edição
                // $relationManagers[] = OrcamentoResource\RelationManagers\AumentosKmRelationManager::class;
                break;
            case 'proprio_nova_rota':
                // Seção Próprios - Nova Rota removida
                // $relationManagers[] = OrcamentoResource\RelationManagers\PropriosNovaRotaRelationManager::class;
                break;
        }
        
        return $relationManagers;
    }
}