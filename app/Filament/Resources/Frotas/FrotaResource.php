<?php

namespace App\Filament\Resources\Frotas;

use App\Filament\Resources\Frotas\Pages\CreateFrota;
use App\Filament\Resources\Frotas\Pages\EditFrota;
use App\Filament\Resources\Frotas\Pages\ListFrotas;
use App\Filament\Resources\Frotas\Schemas\FrotaForm;
use App\Filament\Resources\Frotas\Tables\FrotasTable;
use App\Models\Frota;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FrotaResource extends Resource
{
    protected static ?string $model = Frota::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string | UnitEnum | null $navigationGroup = 'Frotas e Veículos';

    public static function form(Schema $schema): Schema
    {
        return FrotaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FrotasTable::configure($table);
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
            'index' => ListFrotas::route('/'),
            'create' => CreateFrota::route('/create'),
            'edit' => EditFrota::route('/{record}/edit'),
        ];
    }
}
