<?php

namespace App\Filament\Resources\TiposVeiculos\Pages;

use App\Filament\Resources\TiposVeiculos\TipoVeiculoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTipoVeiculo extends EditRecord
{
    protected static string $resource = TipoVeiculoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Excluir'),
        ];
    }

    public function getTitle(): string
    {
        return 'Editar Tipo de Veículo';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Tipo de veículo atualizado com sucesso!';
    }
}