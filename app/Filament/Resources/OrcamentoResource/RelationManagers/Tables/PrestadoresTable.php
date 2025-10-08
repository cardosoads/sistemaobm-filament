<?php

namespace App\Filament\Resources\OrcamentoResource\RelationManagers\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class PrestadoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('fornecedor_nome')
            ->columns([
                Tables\Columns\TextColumn::make('fornecedor_nome')
                    ->label('Fornecedor')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('valor_referencia')
                    ->label('Valor Ref.')
                    ->money('BRL')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('qtd_dias')
                    ->label('Dias')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('lucro_percentual')
                    ->label('Lucro %')
                    ->suffix('%')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Valor Total')
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