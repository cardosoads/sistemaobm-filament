<?php

namespace App\Filament\Resources\Combustivels\Pages;

use App\Filament\Resources\Combustivels\CombustivelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCombustivel extends EditRecord
{
    protected static string $resource = CombustivelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
