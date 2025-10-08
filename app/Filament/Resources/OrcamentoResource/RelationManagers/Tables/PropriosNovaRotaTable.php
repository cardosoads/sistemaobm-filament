<?php

namespace App\Filament\Resources\OrcamentoResource\RelationManagers\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class PropriosNovaRotaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('valor_funcionario')
            ->columns([
                Tables\Columns\TextColumn::make('valor_funcionario')
                    ->label('Valor Funcionário')
                    ->money('BRL')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('valor_aluguel_frota')
                    ->label('Valor Aluguel')
                    ->money('BRL')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('valor_total_geral')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('valor_final')
                    ->label('Valor Final')
                    ->money('BRL')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('grupoImposto.nome')
                    ->label('Grupo Imposto')
                    ->sortable(),
            ])
            ->filters([
                //
            ]);
    }
}