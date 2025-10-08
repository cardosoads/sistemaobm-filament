<?php

namespace App\Filament\Resources\RecursoHumanos\Pages;

use App\Filament\Resources\RecursoHumanos\RecursoHumanoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRecursoHumanos extends ListRecords
{
    protected static string $resource = RecursoHumanoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
