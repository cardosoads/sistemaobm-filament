<?php

namespace App\Filament\Resources\Combustivels;

use App\Filament\Resources\Combustivels\Pages\CreateCombustivel;
use App\Filament\Resources\Combustivels\Pages\EditCombustivel;
use App\Filament\Resources\Combustivels\Pages\ListCombustivels;
use App\Filament\Resources\Combustivels\Schemas\CombustivelForm;
use App\Filament\Resources\Combustivels\Tables\CombustivelsTable;
use App\Models\Combustivel;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CombustivelResource extends Resource
{
    protected static ?string $model = Combustivel::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string | UnitEnum | null $navigationGroup = 'Cadastros';

    public static function form(Schema $schema): Schema
    {
        return CombustivelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CombustivelsTable::configure($table);
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
            'index' => ListCombustivels::route('/'),
            'create' => CreateCombustivel::route('/create'),
            'edit' => EditCombustivel::route('/{record}/edit'),
        ];
    }
}
