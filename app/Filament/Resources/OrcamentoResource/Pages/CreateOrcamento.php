<?php

namespace App\Filament\Resources\OrcamentoResource\Pages;

use App\Filament\Resources\OrcamentoResource;
use App\Models\OrcamentoPrestador;
use App\Models\OrcamentoAumentoKm;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrcamento extends CreateRecord
{
    protected static string $resource = OrcamentoResource::class;
    
    protected array $prestadorData = [];
    protected array $aumentoKmData = [];

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

        return $record;
    }
}