<?php

namespace App\Filament\Resources\Bases\Pages;

use App\Filament\Resources\Bases\BaseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBase extends EditRecord
{
    protected static string $resource = BaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
