<?php

namespace App\Filament\Resources\Marcas;

use App\Filament\Resources\Marcas\Pages;
use App\Filament\Resources\Marcas\Schemas\MarcaForm;

use App\Filament\Resources\Marcas\Schemas\MarcasTable;
use App\Models\Marca;
use BackedEnum;
use UnitEnum;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class MarcaResource extends Resource
{
    protected static ?string $model = Marca::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string | UnitEnum | null $navigationGroup = 'Cadastros';

    protected static ?string $navigationLabel = 'Marcas';

    protected static ?string $modelLabel = 'Marca';

    protected static ?string $pluralModelLabel = 'Marcas';

    public static function form(Schema $schema): Schema
    {
        return MarcaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarcasTable::configure($table);
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
            'index' => Pages\ListMarcas::route('/'),
            'create' => Pages\CreateMarca::route('/create'),
            'view' => Pages\ViewMarca::route('/{record}'),
            'edit' => Pages\EditMarca::route('/{record}/edit'),
        ];
    }
}