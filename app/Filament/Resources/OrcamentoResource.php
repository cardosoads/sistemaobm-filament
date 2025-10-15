<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrcamentoResource\Pages;
use App\Filament\Resources\OrcamentoResource\RelationManagers;
use App\Models\Orcamento;
use BackedEnum;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrcamentoResource extends Resource
{
    protected static ?string $model = Orcamento::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static string | \UnitEnum | null $navigationGroup = 'Orçamentos';
    
    protected static ?string $modelLabel = 'Orçamento';
    
    protected static ?string $pluralModelLabel = 'Orçamentos';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\OrcamentoResource\Schemas\OrcamentoForm::configure($schema);
    }



    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\OrcamentoResource\Tables\OrcamentoTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AumentosKmRelationManager::class,
            RelationManagers\PropriosNovaRotaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrcamentos::route('/'),
            'create' => Pages\CreateOrcamento::route('/create'),
            'view' => Pages\ViewOrcamento::route('/{record}'),
            'edit' => Pages\EditOrcamento::route('/{record}/edit'),
        ];
    }
}