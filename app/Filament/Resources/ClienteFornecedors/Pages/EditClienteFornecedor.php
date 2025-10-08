<?php

namespace App\Filament\Resources\ClienteFornecedors\Pages;

use App\Filament\Resources\ClienteFornecedors\ClienteFornecedorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClienteFornecedor extends EditRecord
{
    protected static string $resource = ClienteFornecedorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
