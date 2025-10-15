<?php

namespace App\Filament\Resources\OrcamentoResource\Pages;

use App\Filament\Resources\OrcamentoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewOrcamento extends ViewRecord
{
    protected static string $resource = OrcamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return \App\Filament\Resources\OrcamentoResource\Schemas\OrcamentoInfolist::configure($schema);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Carregar relacionamentos necessários
        $this->record->load([
            'prestadores.fornecedor',
            'prestadores.grupoImposto',
            'aumentosKm.grupoImposto',
            'propriosNovaRota.funcionario',
            'propriosNovaRota.frota',
            'centroCusto',
            'user'
        ]);

        return $data;
    }

    public function getRelationManagers(): array
    {
        // Não exibir nenhum RelationManager na página de visualização
        // Os dados são mostrados através do Infolist
        return [];
    }
}