<?php

namespace App\Filament\Resources\Obms;

use App\Filament\Resources\Obms\Pages\CreateObm;
use App\Filament\Resources\Obms\Pages\EditObm;
use App\Filament\Resources\Obms\Pages\ListObms;
use App\Filament\Resources\Obms\Schemas\ObmForm;
use App\Filament\Resources\Obms\Tables\ObmsTable;
use App\Models\Obm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ObmResource extends Resource
{
    protected static ?string $model = Obm::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Document;

    public static function form(Schema $schema): Schema
    {
        return ObmForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ObmsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListObms::route('/'),
            'create' => CreateObm::route('/create'),
            'edit' => EditObm::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['orcamento', 'colaborador', 'frota', 'user']);

        $user = auth()->user();
        if ($user) {
            // Ocultar OBMs de prestador e aumento_km para RH e Frotas
            if ($user->hasAnyRole(['Recursos Humanos', 'RH', 'Frotas'])) {
                $query->whereHas('orcamento', function (Builder $subQuery) {
                    $subQuery->whereNotIn('tipo_orcamento', ['prestador', 'aumento_km']);
                });
            }

            if ($user->hasAnyRole(['Frotas'])) {
                // Ver OBMs pendentes de definição de veículo (frota_id nulo), independentemente do colaborador
                $query->whereNull('frota_id');
            } elseif ($user->hasAnyRole(['Recursos Humanos', 'RH'])) {
                // Ver OBMs pendentes de definição de colaborador (colaborador_id nulo), independentemente do veículo
                $query->whereNull('colaborador_id');
            }
        }

        return $query;
    }

    public static function getGloballySearchableAttributes(): array
    {
        // Busca nos campos relacionados do orçamento
        return ['orcamento.nome_rota', 'orcamento.cliente_nome'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Orçamento' => $record->orcamento?->nome_rota,
            'Cliente' => $record->orcamento?->cliente_nome,
            'Origem' => $record->orcamento?->origem,
            'Destino' => $record->orcamento?->destino,
            'Colaborador' => $record->colaborador?->nome,
            'Veículo' => $record->frota?->tipoVeiculo?->nome,
            'Período' => $record->periodo,
            'Status' => $record->status_label,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole([
            'Administrador',
            'Gerente',
            'Fornecedor',
            'Recursos Humanos',
            'Frotas',
        ]);
    }
}
