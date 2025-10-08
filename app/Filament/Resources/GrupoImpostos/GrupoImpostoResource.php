<?php

namespace App\Filament\Resources\GrupoImpostos;

use App\Filament\Resources\GrupoImpostos\Pages\CreateGrupoImposto;
use App\Filament\Resources\GrupoImpostos\Pages\EditGrupoImposto;
use App\Filament\Resources\GrupoImpostos\Pages\ListGrupoImpostos;
use App\Filament\Resources\GrupoImpostos\Schemas\GrupoImpostoForm;
use App\Filament\Resources\GrupoImpostos\Tables\GrupoImpostosTable;
use App\Models\GrupoImposto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use UnitEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GrupoImpostoResource extends Resource
{
    protected static ?string $model = GrupoImposto::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static string | UnitEnum | null $navigationGroup = 'Impostos';

    public static function form(Schema $schema): Schema
    {
        return GrupoImpostoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GrupoImpostosTable::configure($table);
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
            'index' => ListGrupoImpostos::route('/'),
            'create' => CreateGrupoImposto::route('/create'),
            'edit' => EditGrupoImposto::route('/{record}/edit'),
        ];
    }
}
