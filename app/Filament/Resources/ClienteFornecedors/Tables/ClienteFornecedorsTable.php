<?php

namespace App\Filament\Resources\ClienteFornecedors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ClienteFornecedorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Cliente' => 'success',
                        'Fornecedor' => 'info',
                        default => 'gray',
                    })
                    ->sortable(query: function ($query, string $direction): void {
                        $query->orderBy('is_cliente', $direction === 'asc' ? 'desc' : 'asc');
                    }),

                TextColumn::make('codigo_cliente_omie')
                    ->label('Cód. Omie')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('razao_social')
                    ->label('Razão Social')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('nome_fantasia')
                    ->label('Nome Fantasia')
                    ->searchable()
                    ->limit(25)
                    ->toggleable(),

                TextColumn::make('cnpj_cpf')
                    ->label('CNPJ/CPF')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('CNPJ/CPF copiado!')
                    ->copyMessageDuration(1500),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('E-mail copiado!')
                    ->copyMessageDuration(1500)
                    ->toggleable(),

                TextColumn::make('telefone_completo')
                    ->label('Telefone')
                    ->getStateUsing(function ($record) {
                        if ($record->telefone1_ddd && $record->telefone1_numero) {
                            return "({$record->telefone1_ddd}) {$record->telefone1_numero}";
                        }
                        return '-';
                    })
                    ->copyable()
                    ->copyMessage('Telefone copiado!')
                    ->copyMessageDuration(1500)
                    ->toggleable(),

                TextColumn::make('cidade')
                    ->label('Cidade')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('estado')
                    ->label('UF')
                    ->searchable()
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

                TextColumn::make('pessoa_fisica')
                    ->label('Tipo Pessoa')
                    ->formatStateUsing(fn (string $state): string => $state === 'S' ? 'Física' : 'Jurídica')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'S' ? 'info' : 'primary')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('inscricao_estadual')
                    ->label('IE')
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
                SelectFilter::make('is_cliente')
                    ->label('Tipo')
                    ->options([
                        true => 'Cliente',
                        false => 'Fornecedor',
                    ])
                    ->placeholder('Todos'),

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

                SelectFilter::make('pessoa_fisica')
                    ->label('Tipo de Pessoa')
                    ->options([
                        'N' => 'Jurídica',
                        'S' => 'Física',
                    ])
                    ->placeholder('Todos'),

                SelectFilter::make('importado_api')
                    ->label('Origem')
                    ->options([
                        'S' => 'Importado da API',
                        'N' => 'Manual',
                    ])
                    ->placeholder('Todos'),

                TernaryFilter::make('has_email')
                    ->label('Possui E-mail')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('email')->where('email', '!=', ''),
                        false: fn ($query) => $query->whereNull('email')->orWhere('email', ''),
                        blank: fn ($query) => $query,
                    ),

                TernaryFilter::make('has_phone')
                    ->label('Possui Telefone')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('telefone1_numero')->where('telefone1_numero', '!=', ''),
                        false: fn ($query) => $query->whereNull('telefone1_numero')->orWhere('telefone1_numero', ''),
                        blank: fn ($query) => $query,
                    ),
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
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
