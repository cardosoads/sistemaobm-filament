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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';
    
    protected static string | \UnitEnum | null $navigationGroup = 'Operações';
    
    protected static ?string $navigationLabel = 'OBMs - Ordens de Movimentação';
    
    protected static ?string $modelLabel = 'OBM';
    
    protected static ?string $pluralModelLabel = 'OBMs';

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
        return parent::getEloquentQuery()
            ->with(['orcamento', 'colaborador', 'frota', 'user']);
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
