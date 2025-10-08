<?php

namespace App\Filament\Resources\Impostos\Pages;

use App\Filament\Resources\Impostos\ImpostoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditImposto extends EditRecord
{
    protected static string $resource = ImpostoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
