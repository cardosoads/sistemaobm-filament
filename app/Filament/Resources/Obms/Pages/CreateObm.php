<?php

namespace App\Filament\Resources\Obms\Pages;

use App\Filament\Resources\Obms\ObmResource;
use App\Models\Obm;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateObm extends CreateRecord
{
    protected static string $resource = ObmResource::class;
    
    protected function beforeCreate(): void
    {
        $data = $this->data;
        
        // Validar sobreposição de datas para colaborador
        if (!empty($data['colaborador_id'])) {
            $colaboradorSobreposicao = !Obm::validarSobreposicaoColaborador(
                $data['colaborador_id'],
                $data['data_inicio'],
                $data['data_fim']
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
        
        // Validar sobreposição de datas para veículo
        if (!empty($data['frota_id'])) {
            $veiculoSobreposicao = !Obm::validarSobreposicaoVeiculo(
                $data['frota_id'],
                $data['data_inicio'],
                $data['data_fim']
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
        
        // Verificar se o orçamento está aprovado
        if (!empty($data['orcamento_id'])) {
            $orcamento = \App\Models\Orcamento::find($data['orcamento_id']);
            if ($orcamento && $orcamento->status !== 'aprovado') {
                Notification::make()
                    ->title('Erro de Validação')
                    ->body('Apenas orçamentos aprovados podem ser usados para criar OBMs.')
                    ->danger()
                    ->send();
                    
                $this->halt();
            }
        }
        
        // Definir o usuário logado como criador
        $this->data['user_id'] = auth()->id();
    }
    
    protected function afterCreate(): void
    {
        // Exibição dos dados do orçamento será feita via relacionamento; nada a consolidar.
    }
}
