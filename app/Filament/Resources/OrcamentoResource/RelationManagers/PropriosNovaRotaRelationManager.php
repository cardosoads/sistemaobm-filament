<?php

namespace App\Filament\Resources\OrcamentoResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class PropriosNovaRotaRelationManager extends RelationManager
{
    protected static string $relationship = 'propriosNovaRota';

    protected static ?string $title = 'Próprios - Nova Rota';

    public function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\OrcamentoResource\RelationManagers\Schemas\PropriosNovaRotaForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return \App\Filament\Resources\OrcamentoResource\RelationManagers\Tables\PropriosNovaRotaTable::configure($table);
    }
}