<?php

namespace App\Filament\Resources\RecursoHumanos\Pages;

use App\Filament\Resources\RecursoHumanos\RecursoHumanoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRecursoHumano extends EditRecord
{
    protected static string $resource = RecursoHumanoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
