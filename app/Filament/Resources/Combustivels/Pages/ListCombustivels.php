<?php

namespace App\Filament\Resources\Combustivels\Pages;

use App\Filament\Resources\Combustivels\CombustivelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCombustivels extends ListRecords
{
    protected static string $resource = CombustivelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
