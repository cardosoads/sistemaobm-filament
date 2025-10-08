<?php

namespace App\Filament\Resources\Impostos;

use App\Filament\Resources\Impostos\Pages\CreateImposto;
use App\Filament\Resources\Impostos\Pages\EditImposto;
use App\Filament\Resources\Impostos\Pages\ListImpostos;
use App\Filament\Resources\Impostos\Schemas\ImpostoForm;
use App\Filament\Resources\Impostos\Tables\ImpostosTable;
use App\Models\Imposto;
use BackedEnum;
use Filament\Resources\Resource;
use UnitEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ImpostoResource extends Resource
{
    protected static ?string $model = Imposto::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static string | UnitEnum | null $navigationGroup = 'Impostos';

    protected static ?string $recordTitleAttribute = 'nome';

    protected static ?string $navigationLabel = 'Impostos';

    protected static ?string $modelLabel = 'Imposto';

    protected static ?string $pluralModelLabel = 'Impostos';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return ImpostoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ImpostosTable::configure($table);
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
            'index' => ListImpostos::route('/'),
            'create' => CreateImposto::route('/create'),
            'edit' => EditImposto::route('/{record}/edit'),
        ];
    }
}
