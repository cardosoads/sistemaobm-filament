<?php

namespace App\Filament\Resources\OrcamentoResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class PrestadoresRelationManager extends RelationManager
{
    protected static string $relationship = 'prestadores';

    protected static ?string $title = 'Prestadores';

    public function form(Schema $schema): Schema
    {
        return $schema->schema(
            \App\Filament\Resources\OrcamentoResource\RelationManagers\Schemas\PrestadoresForm::schema()
        );
    }

    public function table(Table $table): Table
    {
        return \App\Filament\Resources\OrcamentoResource\RelationManagers\Tables\PrestadoresTable::configure($table);
    }
}