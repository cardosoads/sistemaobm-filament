<?php

namespace App\Filament\Resources\Colaboradores;

use App\Filament\Resources\Colaboradores\Pages\CreateColaborador;
use App\Filament\Resources\Colaboradores\Pages\EditColaborador;
use App\Filament\Resources\Colaboradores\Pages\ListColaboradors;
use App\Filament\Resources\Colaboradores\Schemas\ColaboradorForm;
use App\Filament\Resources\Colaboradores\Tables\ColaboradorsTable;
use App\Models\Colaborador;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ColaboradorResource extends Resource
{
    protected static ?string $model = Colaborador::class;

    protected static string | UnitEnum | null $navigationGroup = 'Recursos Humanos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Colaboradores';
    protected static ?string $modelLabel = 'Colaborador';
    protected static ?string $pluralModelLabel = 'Colaboradores';

    public static function form(Schema $schema): Schema
    {
        return ColaboradorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ColaboradorsTable::configure($table);
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
            'index' => ListColaboradors::route('/'),
            'create' => CreateColaborador::route('/create'),
            'edit' => EditColaborador::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['Administrador', 'Recursos Humanos']);
    }
}
