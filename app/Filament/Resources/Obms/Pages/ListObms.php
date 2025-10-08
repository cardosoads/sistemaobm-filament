<?php

namespace App\Filament\Resources\Obms\Pages;

use App\Filament\Resources\Obms\ObmResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListObms extends ListRecords
{
    protected static string $resource = ObmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
