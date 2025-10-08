<?php

namespace App\Filament\Resources\GrupoImpostos\Pages;

use App\Filament\Resources\GrupoImpostos\GrupoImpostoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGrupoImposto extends EditRecord
{
    protected static string $resource = GrupoImpostoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
