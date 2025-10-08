<?php

namespace App\Filament\Resources\Colaboradores\Pages;

use App\Filament\Resources\Colaboradores\ColaboradorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditColaborador extends EditRecord
{
    protected static string $resource = ColaboradorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
