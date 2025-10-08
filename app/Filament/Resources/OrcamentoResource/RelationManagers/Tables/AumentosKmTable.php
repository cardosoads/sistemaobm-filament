<?php

namespace App\Filament\Resources\OrcamentoResource\RelationManagers\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class AumentosKmTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('km_rodado')
            ->columns([
                Tables\Columns\TextColumn::make('km_rodado')
                    ->label('KM Rodado')
                    ->suffix(' km')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('km_extra')
                    ->label('KM Extra')
                    ->suffix(' km')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('litros_combustivel')
                    ->label('Litros Comb.')
                    ->suffix(' L')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('horas_extras')
                    ->label('Horas Extras')
                    ->suffix(' h')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('valor_pedagio')
                    ->label('Pedágio')
                    ->money('BRL')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('valor_total')
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