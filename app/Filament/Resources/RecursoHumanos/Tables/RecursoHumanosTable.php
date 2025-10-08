<?php

namespace App\Filament\Resources\RecursoHumanos\Tables;

use App\Models\RecursoHumano;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RecursoHumanosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cargo')
                    ->label('Cargo')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('tipo_contratacao')
                    ->label('Tipo de Contratação')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('base.base')
                    ->label('Base')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Não informado'),
                
                TextColumn::make('salario_base')
                    ->label('Salário Base')
                    ->money('BRL')
                    ->sortable(),
                
                TextColumn::make('total_adicionais')
                    ->label('Total Adicionais')
                    ->money('BRL')
                    ->getStateUsing(function ($record) {
                        return $record->insalubridade + $record->periculosidade + 
                               $record->horas_extras + $record->adicional_noturno + $record->extras;
                    }),
                
                TextColumn::make('beneficios')
                    ->label('Benefícios')
                    ->money('BRL')
                    ->sortable(),
                
                TextColumn::make('custo_total_mao_obra')
                    ->label('Custo Total')
                    ->money('BRL')
                    ->sortable()
                    ->weight('bold'),
                
                ToggleColumn::make('active')
                    ->label('Ativo'),
                
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('cargo')
                    ->label('Cargo')
                    ->options(RecursoHumano::getCargos()),
                
                SelectFilter::make('tipo_contratacao')
                    ->label('Tipo de Contratação')
                    ->options(RecursoHumano::getTiposContratacao()),
                
                SelectFilter::make('base_id')
                    ->label('Base')
                    ->relationship('base', 'base'),
                
                TernaryFilter::make('active')
                    ->label('Status')
                    ->placeholder('Todos')
                    ->trueLabel('Apenas ativos')
                    ->falseLabel('Apenas inativos'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchOnBlur()
            ->striped();
    }
}
