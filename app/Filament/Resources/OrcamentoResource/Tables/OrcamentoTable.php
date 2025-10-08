<?php

namespace App\Filament\Resources\OrcamentoResource\Tables;

use Filament\Forms;
use Filament\Tables;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrcamentoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_orcamento')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('nome_rota')
                    ->label('Rota')
                    ->searchable()
                    ->limit(30),
                    
                Tables\Columns\TextColumn::make('cliente_nome')
                    ->label('Cliente')
                    ->searchable()
                    ->limit(25),
                    
                Tables\Columns\BadgeColumn::make('tipo_orcamento')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'prestador',
                        'warning' => 'aumento_km',
                        'success' => 'proprio_nova_rota',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'prestador' => 'Prestador',
                        'aumento_km' => 'Aumento KM',
                        'proprio_nova_rota' => 'Próprio Nova Rota',
                        default => $state,
                    })
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pendente',
                        'success' => 'aprovado',
                        'danger' => 'rejeitado',
                        'gray' => 'cancelado',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pendente' => 'Pendente',
                        'aprovado' => 'Aprovado',
                        'rejeitado' => 'Rejeitado',
                        'cancelado' => 'Cancelado',
                        default => $state,
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('valor_final')
                    ->label('Valor Final')
                    ->money('BRL')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('data_solicitacao')
                    ->label('Data Solicitação')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Responsável')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_orcamento')
                    ->label('Tipo de Orçamento')
                    ->options([
                        'prestador' => 'Prestador',
                        'aumento_km' => 'Aumento de KM',
                        'proprio_nova_rota' => 'Próprio - Nova Rota',
                    ]),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pendente' => 'Pendente',
                        'aprovado' => 'Aprovado',
                        'rejeitado' => 'Rejeitado',
                        'cancelado' => 'Cancelado',
                    ]),
                    
                Tables\Filters\Filter::make('data_solicitacao')
                    ->label('Data da Solicitação')
                    ->form([
                        Forms\Components\DatePicker::make('data_inicio')
                            ->label('Data Início'),
                        Forms\Components\DatePicker::make('data_fim')
                            ->label('Data Fim'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['data_inicio'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data_solicitacao', '>=', $date),
                            )
                            ->when(
                                $data['data_fim'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data_solicitacao', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}