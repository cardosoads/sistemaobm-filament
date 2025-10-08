<?php

namespace App\Filament\Resources\Frotas\Pages;

use App\Filament\Resources\Frotas\FrotaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFrota extends EditRecord
{
    protected static string $resource = FrotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
