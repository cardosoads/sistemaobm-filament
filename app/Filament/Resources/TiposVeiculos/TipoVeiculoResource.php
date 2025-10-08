<?php

namespace App\Filament\Resources\TiposVeiculos;

use App\Filament\Resources\TiposVeiculos\Pages\CreateTipoVeiculo;
use App\Filament\Resources\TiposVeiculos\Pages\EditTipoVeiculo;
use App\Filament\Resources\TiposVeiculos\Pages\ListTiposVeiculos;
use App\Filament\Resources\TiposVeiculos\Schemas\TipoVeiculoForm;
use App\Filament\Resources\TiposVeiculos\Tables\TiposVeiculosTable;
use App\Models\TipoVeiculo;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TipoVeiculoResource extends Resource
{
    protected static ?string $model = TipoVeiculo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string | UnitEnum | null $navigationGroup = 'Frotas e Veículos';

    protected static ?string $navigationLabel = 'Tipos de Veículos';

    protected static ?string $modelLabel = 'Tipo de Veículo';

    protected static ?string $pluralModelLabel = 'Tipos de Veículos';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return TipoVeiculoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TiposVeiculosTable::configure($table);
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
            'index' => ListTiposVeiculos::route('/'),
            'create' => CreateTipoVeiculo::route('/create'),
            'edit' => EditTipoVeiculo::route('/{record}/edit'),
        ];
    }
}