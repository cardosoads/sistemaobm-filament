<?php

namespace App\Filament\Resources\ClienteFornecedors;

use App\Filament\Resources\ClienteFornecedors\Pages\CreateClienteFornecedor;
use App\Filament\Resources\ClienteFornecedors\Pages\EditClienteFornecedor;
use App\Filament\Resources\ClienteFornecedors\Pages\ListClienteFornecedors;
use App\Filament\Resources\ClienteFornecedors\Schemas\ClienteFornecedorForm;
use App\Filament\Resources\ClienteFornecedors\Tables\ClienteFornecedorsTable;
use App\Models\ClienteFornecedor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClienteFornecedorResource extends Resource
{
    protected static ?string $model = ClienteFornecedor::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Clientes e Fornecedores';

    protected static ?string $modelLabel = 'Cliente/Fornecedor';

    protected static ?string $pluralModelLabel = 'Clientes e Fornecedores';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ClienteFornecedorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClienteFornecedorsTable::configure($table);
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
            'index' => ListClienteFornecedors::route('/'),
            'create' => CreateClienteFornecedor::route('/create'),
            'edit' => EditClienteFornecedor::route('/{record}/edit'),
        ];
    }
}
