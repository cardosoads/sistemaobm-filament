<?php

namespace App\Filament\Resources\GrupoImpostos\Pages;

use App\Filament\Resources\GrupoImpostos\GrupoImpostoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGrupoImpostos extends ListRecords
{
    protected static string $resource = GrupoImpostoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
