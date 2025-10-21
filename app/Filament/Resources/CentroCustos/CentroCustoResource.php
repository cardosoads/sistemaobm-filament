<?php

namespace App\Filament\Resources\CentroCustos;

use App\Filament\Resources\CentroCustos\Pages\CreateCentroCusto;
use App\Filament\Resources\CentroCustos\Pages\EditCentroCusto;
use App\Filament\Resources\CentroCustos\Pages\ListCentroCustos;
use App\Filament\Resources\CentroCustos\Schemas\CentroCustoForm;
use App\Filament\Resources\CentroCustos\Tables\CentroCustosTable;
use App\Models\CentroCusto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CentroCustoResource extends Resource
{
    protected static ?string $model = CentroCusto::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Centros de Custo';

    protected static ?string $modelLabel = 'Centro de Custo';

    protected static ?string $pluralModelLabel = 'Centros de Custo';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CentroCustoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CentroCustosTable::configure($table);
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
            'index' => ListCentroCustos::route('/'),
            'create' => CreateCentroCusto::route('/create'),
            'edit' => EditCentroCusto::route('/{record}/edit'),
        ];
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
