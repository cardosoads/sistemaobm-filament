<?php

namespace App\Filament\Resources\Bases\Pages;

use App\Filament\Resources\Bases\BaseResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBase extends ViewRecord
{
    protected static string $resource = BaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
