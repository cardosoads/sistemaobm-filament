<?php

namespace App\Filament\Resources\Frotas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use App\Models\TipoVeiculo;

class FrotasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tipoVeiculo.codigo')
                    ->label('Tipo de Veículo')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('fipe')
                    ->label('Valor FIPE')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('aluguel_carro')
                    ->label('Aluguel')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('rastreador')
                    ->label('Rastreador')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('provisoes_avarias')
                    ->label('Provisões Avarias')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('provisao_desmobilizacao')
                    ->label('Provisão Desmobilização')
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('provisao_diaria_rac')
                    ->label('Provisão Diária RAC')
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('custo_total')
                    ->label('Custo Total')
                    ->money('BRL')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                IconColumn::make('active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo_veiculo_id')
                    ->label('Tipo de Veículo')
                    ->options(TipoVeiculo::pluck('codigo', 'id'))
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('active')
                    ->label('Status')
                    ->placeholder('Todos')
                    ->trueLabel('Ativos')
                    ->falseLabel('Inativos'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
