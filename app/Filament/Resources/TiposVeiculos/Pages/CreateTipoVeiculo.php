<?php

namespace App\Filament\Resources\TiposVeiculos\Pages;

use App\Filament\Resources\TiposVeiculos\TipoVeiculoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTipoVeiculo extends CreateRecord
{
    protected static string $resource = TipoVeiculoResource::class;

    public function getTitle(): string
    {
        return 'Criar Tipo de Veículo';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Tipo de veículo criado com sucesso!';
    }
}