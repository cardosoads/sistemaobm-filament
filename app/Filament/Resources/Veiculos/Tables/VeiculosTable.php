<?php

namespace App\Filament\Resources\Veiculos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VeiculosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('placa')
                    ->label('Placa')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('marca_modelo')
                    ->label('Marca/Modelo')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('ano_modelo')
                    ->label('Ano/Modelo')
                    ->sortable(),
                
                TextColumn::make('cor')
                    ->label('Cor')
                    ->searchable(),
                
                TextColumn::make('tipoVeiculo.nome')
                    ->label('Tipo')
                    ->sortable(),
                
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'ativo',
                        'danger' => 'inativo',
                        'warning' => 'manutencao',
                        'gray' => 'vendido',
                    ])
                    ->sortable(),
                
                TextColumn::make('tipo_combustivel')
                    ->label('Combustível')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
