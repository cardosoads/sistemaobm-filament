<?php

namespace App\Filament\Resources\Bases\Pages;

use App\Filament\Resources\Bases\BaseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBases extends ListRecords
{
    protected static string $resource = BaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
