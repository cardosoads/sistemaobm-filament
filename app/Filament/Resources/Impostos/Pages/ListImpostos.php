<?php

namespace App\Filament\Resources\Impostos\Pages;

use App\Filament\Resources\Impostos\ImpostoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListImpostos extends ListRecords
{
    protected static string $resource = ImpostoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
