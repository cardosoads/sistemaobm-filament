<?php

namespace App\Filament\Resources\CentroCustos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CentroCustosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_departamento_omie')
                    ->label('Cód. Omie')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('cliente.razao_social')
                    ->label('Cliente Associado')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('Não associado')
                    ->toggleable(),

                TextColumn::make('base.base')
                    ->label('Base')
                    ->searchable(['base.base', 'base.sigla', 'base.regional', 'base.uf'])
                    ->formatStateUsing(fn ($record) => 
                        $record->base 
                            ? "{$record->base->base} ({$record->base->sigla}) - {$record->base->regional}/{$record->base->uf}"
                            : 'Não definida'
                    )
                    ->limit(40)
                    ->placeholder('Não definida')
                    ->toggleable(),

                TextColumn::make('supervisor')
                    ->label('Supervisor')
                    ->searchable()
                    ->limit(25)
                    ->placeholder('Não definido')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Ativo' => 'success',
                        'Inativo' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(query: function ($query, string $direction): void {
                        $query->orderBy('inativo', $direction === 'asc' ? 'asc' : 'desc');
                    }),

                TextColumn::make('status_sincronizacao')
                    ->label('Sincronização')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sincronizado' => 'success',
                        'pendente' => 'warning',
                        'erro' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('ultima_sincronizacao')
                    ->label('Última Sync')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('tipo_importacao')
                    ->label('Origem')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Importado' => 'info',
                        'Manual' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('codigo_departamento_integracao')
                    ->label('Cód. Integração')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('data_inclusao')
                    ->label('Data Inclusão')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('data_alteracao')
                    ->label('Data Alteração')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                SelectFilter::make('inativo')
                    ->label('Status')
                    ->options([
                        'N' => 'Ativo',
                        'S' => 'Inativo',
                    ])
                    ->placeholder('Todos'),

                SelectFilter::make('status_sincronizacao')
                    ->label('Status Sincronização')
                    ->options([
                        'sincronizado' => 'Sincronizado',
                        'pendente' => 'Pendente',
                        'erro' => 'Erro',
                    ])
                    ->placeholder('Todos'),

                SelectFilter::make('importado_api')
                    ->label('Origem')
                    ->options([
                        'S' => 'Importado da API',
                        'N' => 'Manual',
                    ])
                    ->placeholder('Todos'),

                SelectFilter::make('cliente_id')
                    ->label('Cliente Associado')
                    ->relationship('cliente', 'razao_social', fn ($query) => $query->where('is_cliente', true)->where('inativo', 'N'))
                    ->searchable()
                    ->placeholder('Todos'),

                SelectFilter::make('base_id')
                    ->label('Base')
                    ->relationship(
                        'base', 
                        'base',
                        fn ($query) => $query->where('status', true)
                            ->orderBy('regional')
                            ->orderBy('base')
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => 
                        "{$record->base} ({$record->sigla}) - {$record->regional}/{$record->uf}"
                    )
                    ->searchable(['base', 'sigla', 'regional', 'uf'])
                    ->placeholder('Todas'),
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
            ->defaultSort('nome', 'asc');
    }
}
