<?php

namespace App\Filament\Resources\Frotas\Pages;

use App\Filament\Resources\Frotas\FrotaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFrotas extends ListRecords
{
    protected static string $resource = FrotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
