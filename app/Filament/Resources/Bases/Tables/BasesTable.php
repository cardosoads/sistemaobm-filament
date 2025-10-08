<?php

namespace App\Filament\Resources\Bases\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uf')
                    ->label('UF')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('base')
                    ->label('BASE (Cidade)')
                    ->sortable()
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('regional')
                    ->label('Regional')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Norte' => 'success',
                        'Nordeste' => 'warning',
                        'Centro-Oeste' => 'info',
                        'Sudeste' => 'primary',
                        'Sul' => 'secondary',
                        default => 'gray',
                    }),

                TextColumn::make('sigla')
                    ->label('Sigla')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Sigla copiada!')
                    ->weight('bold'),

                IconColumn::make('status')
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
                SelectFilter::make('uf')
                    ->label('UF')
                    ->options([
                        'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
                        'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
                        'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul',
                        'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
                        'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
                        'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
                        'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins',
                    ]),

                SelectFilter::make('regional')
                    ->label('Regional')
                    ->options([
                        'Norte' => 'Norte',
                        'Nordeste' => 'Nordeste',
                        'Centro-Oeste' => 'Centro-Oeste',
                        'Sudeste' => 'Sudeste',
                        'Sul' => 'Sul',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Ativa',
                        0 => 'Inativa',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('uf', 'asc');
    }
}
