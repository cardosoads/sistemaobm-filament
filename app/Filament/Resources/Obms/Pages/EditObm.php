<?php

namespace App\Filament\Resources\Obms\Pages;

use App\Filament\Resources\Obms\ObmResource;
use App\Models\Obm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditObm extends EditRecord
{
    protected static string $resource = ObmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
    
    protected function beforeSave(): void
    {
        $data = $this->data;
        
        // Validar sobreposição de datas para colaborador (ignorando a OBM atual)
        if (!empty($data['colaborador_id'])) {
            $colaboradorSobreposicao = !Obm::validarSobreposicaoColaborador(
                $data['colaborador_id'],
                $data['data_inicio'],
                $data['data_fim'],
                $this->record->id
            );
            
            if ($colaboradorSobreposicao) {
                Notification::make()
                    ->title('Erro de Validação')
                    ->body('O colaborador já está alocado em outra OBM no período selecionado.')
                    ->danger()
                    ->send();
                    
                $this->halt();
            }
        }
        
        // Validar sobreposição de datas para veículo (ignorando a OBM atual)
        if (!empty($data['frota_id'])) {
            $veiculoSobreposicao = !Obm::validarSobreposicaoVeiculo(
                $data['frota_id'],
                $data['data_inicio'],
                $data['data_fim'],
                $this->record->id
            );
            
            if ($veiculoSobreposicao) {
                Notification::make()
                    ->title('Erro de Validação')
                    ->body('O veículo já está alocado em outra OBM no período selecionado.')
                    ->danger()
                    ->send();
                    
                $this->halt();
            }
        }
    }
    
    protected function afterSave(): void
    {
        // Consolidar dados do orçamento após salvar
        $this->record->consolidarDadosOrcamento();
    }
}
