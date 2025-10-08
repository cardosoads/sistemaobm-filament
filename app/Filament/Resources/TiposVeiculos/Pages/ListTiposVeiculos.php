<?php

namespace App\Filament\Resources\TiposVeiculos\Pages;

use App\Filament\Resources\TiposVeiculos\TipoVeiculoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTiposVeiculos extends ListRecords
{
    protected static string $resource = TipoVeiculoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo Tipo de Veículo'),
        ];
    }

    public function getTitle(): string
    {
        return 'Tipos de Veículos';
    }
}