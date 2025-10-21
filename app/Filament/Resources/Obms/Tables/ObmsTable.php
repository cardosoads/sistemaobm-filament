<?php

namespace App\Filament\Resources\Obms\Tables;

use App\Models\Obm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ObmsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Columns\TextColumn::make('orcamento.nome_rota')
                    ->label('Rota')
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => 
                        $record->orcamento?->origem && $record->orcamento?->destino 
                            ? $record->orcamento->origem . ' → ' . $record->orcamento->destino
                            : ($record->origem && $record->destino 
                                ? $record->origem . ' → ' . $record->destino 
                                : 'Origem/Destino não informado')
                    ),

                Columns\TextColumn::make('orcamento.cliente_nome')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->description(fn ($record) => 
                        $record->orcamento?->clienteFornecedor?->nome_fantasia ?? ''
                    ),

                Columns\TextColumn::make('orcamento.tipo_orcamento')
                    ->label('Tipo')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'prestador' => 'Prestador',
                        'aumento_km' => 'Aumento KM',
                        'proprio_nova_rota' => 'Nova Rota',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'prestador' => 'info',
                        'aumento_km' => 'warning',
                        'proprio_nova_rota' => 'success',
                        default => 'gray',
                    }),

                Columns\TextColumn::make('prestador_nome')
                    ->label('Prestador')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        // Buscar prestador do orçamento
                        $orcamento = $record->orcamento;
                        if (!$orcamento || $orcamento->tipo_orcamento !== 'prestador') {
                            return null;
                        }
                        
                        // Buscar o primeiro prestador do orçamento
                        $prestador = $orcamento->prestadores()->with('fornecedor')->first();
                        if ($prestador && $prestador->fornecedor) {
                            return $prestador->fornecedor->razao_social;
                        }
                        
                        return null;
                    })
                    ->placeholder('N/A')
                    ->toggleable(),

                Columns\TextColumn::make('colaborador.nome')
                    ->label('Colaborador')
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => $record->colaborador?->cargo ?? '')
                    ->placeholder('N/A')
                    ->visible(fn ($record) => 
                        !$record || !$record->orcamento || 
                        !in_array($record->orcamento->tipo_orcamento, ['prestador', 'aumento_km'])
                    ),

                Columns\TextColumn::make('frota.tipoVeiculo.nome')
                    ->label('Veículo')
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => $record->frota?->fipe ?? '')
                    ->placeholder('N/A')
                    ->visible(fn ($record) => 
                        !$record || !$record->orcamento || 
                        !in_array($record->orcamento->tipo_orcamento, ['prestador', 'aumento_km'])
                    ),

                Columns\TextColumn::make('data_inicio')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),

                Columns\TextColumn::make('data_fim')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->sortable(),

                Columns\TextColumn::make('duracao_dias')
                    ->label('Dias')
                    ->sortable()
                    ->toggleable(),

                Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pendente' => 'Pendente',
                        'em_andamento' => 'Em Andamento',
                        'concluida' => 'Concluída',
                        default => ucfirst(str_replace('_', ' ', (string) $state)),
                    })
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'pendente' => 'warning',
                        'em_andamento' => 'primary',
                        'concluida' => 'success',
                        default => 'gray',
                    }),

                Columns\TextColumn::make('valor_final')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(),

                Columns\TextColumn::make('user.name')
                    ->label('Criado por')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pendente' => 'Pendente',
                        'em_andamento' => 'Em Andamento',
                        'concluida' => 'Concluída',
                    ])
                    ->multiple(),

                SelectFilter::make('tipo_orcamento')
                    ->label('Tipo de Orçamento')
                    ->relationship('orcamento', 'tipo_orcamento')
                    ->options([
                        'prestador' => 'Prestador',
                        'aumento_km' => 'Aumento KM',
                        'proprio_nova_rota' => 'Nova Rota',
                    ])
                    ->multiple(),

                Filter::make('periodo')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('data_inicio_de')
                            ->label('Início de:'),
                        \Filament\Forms\Components\DatePicker::make('data_inicio_ate')
                            ->label('Até:'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['data_inicio_de'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data_inicio', '>=', $date),
                            )
                            ->when(
                                $data['data_inicio_ate'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data_inicio', '<=', $date),
                            );
                    }),

                Filter::make('colaborador')
                    ->form([
                        \Filament\Forms\Components\Select::make('colaborador_id')
                            ->label('Colaborador')
                            ->relationship('colaborador', 'nome', function (Builder $query) {
                                $query->where('status', true)->orderBy('nome');
                            })
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['colaborador_id'],
                            fn (Builder $query, $id): Builder => $query->where('colaborador_id', $id),
                        );
                    }),

                Filter::make('veiculo')
                    ->form([
                        \Filament\Forms\Components\Select::make('frota_id')
                            ->label('Veículo')
                            ->relationship('frota', 'id', function (Builder $query) {
                                $query->where('active', true)->with(['tipoVeiculo'])->orderBy('tipo_veiculo_id');
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                "{$record->tipoVeiculo->nome} - {$record->fipe}"
                            )
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['frota_id'],
                            fn (Builder $query, $id): Builder => $query->where('frota_id', $id),
                        );
                    }),
            ])
            ->defaultSort('created_at', 'desc')
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
