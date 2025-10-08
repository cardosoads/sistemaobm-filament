<?php

namespace App\Filament\Resources\RecursoHumanos;

use App\Filament\Resources\RecursoHumanos\Pages\CreateRecursoHumano;
use App\Filament\Resources\RecursoHumanos\Pages\EditRecursoHumano;
use App\Filament\Resources\RecursoHumanos\Pages\ListRecursoHumanos;
use App\Filament\Resources\RecursoHumanos\Schemas\RecursoHumanoForm;
use App\Filament\Resources\RecursoHumanos\Tables\RecursoHumanosTable;
use App\Models\RecursoHumano;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use UnitEnum;

use Filament\Tables\Table;

class RecursoHumanoResource extends Resource
{
    protected static ?string $model = RecursoHumano::class;

    protected static string | UnitEnum | null $navigationGroup = 'Recursos Humanos';

    protected static ?string $navigationLabel = 'Cargos';
    
    protected static ?string $modelLabel = 'Cargo';
    
    protected static ?string $pluralModelLabel = 'Cargos';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Schema $schema): Schema
    {
        return RecursoHumanoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecursoHumanosTable::configure($table);
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
            'index' => ListRecursoHumanos::route('/'),
            'create' => CreateRecursoHumano::route('/create'),
            'edit' => EditRecursoHumano::route('/{record}/edit'),
        ];
    }
}
