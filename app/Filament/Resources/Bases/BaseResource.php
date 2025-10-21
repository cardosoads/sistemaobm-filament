<?php

namespace App\Filament\Resources\Bases;

use App\Filament\Resources\Bases\Pages;
use App\Filament\Resources\Bases\Schemas\BaseForm;
use App\Filament\Resources\Bases\Schemas\BaseInfolist;
use App\Filament\Resources\Bases\Tables\BasesTable;
use App\Models\Base;
use BackedEnum;
use Filament\Infolists\Infolist;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BaseResource extends Resource
{
    protected static ?string $model = Base::class;

    protected static ?string $recordTitleAttribute = 'base';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string | UnitEnum | null $navigationGroup = 'Cadastros';

    public static function form(Schema $schema): Schema
    {
        return BaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BasesTable::configure($table);
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
            'index' => Pages\ListBases::route('/'),
            'create' => Pages\CreateBase::route('/create'),
            'view' => Pages\ViewBase::route('/{record}'),
            'edit' => Pages\EditBase::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Bases';
    }

    public static function getModelLabel(): string
    {
        return 'Base';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Bases';
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->hasRole('Administrador');
    }
}