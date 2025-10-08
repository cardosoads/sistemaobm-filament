<?php

namespace App\Filament\Resources\Veiculos\Pages;

use App\Filament\Resources\Veiculos\VeiculoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVeiculo extends EditRecord
{
    protected static string $resource = VeiculoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
