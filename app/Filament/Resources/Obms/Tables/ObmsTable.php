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
use App\Filament\Resources\OrcamentoResource; // import para linkar orçamento
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction; // exportação Excel
use pxlrbt\FilamentExcel\Exports\ExcelExport;

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

                Columns\TextColumn::make('orcamento.numero_orcamento')
                    ->label('Número do Orçamento')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->url(fn ($record) => $record ? OrcamentoResource::getUrl('edit', ['record' => $record->orcamento_id]) : null)
                    ->openUrlInNewTab(),

                Columns\TextColumn::make('orcamento.nome_rota')
                    ->label('Rota')
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => 
                        $record?->orcamento?->origem && $record?->orcamento?->destino 
                            ? ($record?->orcamento?->origem . ' → ' . $record?->orcamento?->destino)
                            : ($record?->origem && $record?->destino 
                                ? ($record?->origem . ' → ' . $record?->destino)
                                : 'Origem/Destino não informado')
                    ),

                Columns\TextColumn::make('orcamento.cliente_nome')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->description(fn ($record) => 
                        $record?->orcamento?->clienteFornecedor?->nome_fantasia ?? ''
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
                        $orcamento = $record?->orcamento;
                        if (!$orcamento) {
                            return null;
                        }
                        
                        // Para tipo prestador, buscar na tabela orcamento_prestador
                        if ($orcamento->tipo_orcamento === 'prestador') {
                            // Carregar o relacionamento com eager loading
                            $prestador = $orcamento->prestadores()->with('fornecedor')->first();
                            if ($prestador) {
                                // Priorizar o nome salvo diretamente no campo fornecedor_nome
                                if (!empty($prestador->fornecedor_nome)) {
                                    return $prestador->fornecedor_nome;
                                }
                                // Fallback: buscar pelo relacionamento fornecedor
                                if ($prestador->fornecedor) {
                                    return $prestador->fornecedor->nome_fantasia 
                                        ?? $prestador->fornecedor->razao_social 
                                        ?? null;
                                }
                            }
                        }
                        
                        // Para nova rota, verificar se incluir_fornecedor está marcado
                        if ($orcamento->tipo_orcamento === 'proprio_nova_rota') {
                            $proprioNovaRota = $orcamento->propriosNovaRota()->with('fornecedor')->first();
                            if ($proprioNovaRota && $proprioNovaRota->incluir_fornecedor) {
                                // Priorizar o nome salvo diretamente no campo fornecedor_nome
                                if (!empty($proprioNovaRota->fornecedor_nome)) {
                                    return $proprioNovaRota->fornecedor_nome;
                                }
                                // Fallback: buscar pelo relacionamento fornecedor
                                if ($proprioNovaRota->fornecedor) {
                                    return $proprioNovaRota->fornecedor->nome_fantasia 
                                        ?? $proprioNovaRota->fornecedor->razao_social 
                                        ?? null;
                                }
                            }
                        }
                        
                        return null;
                    })
                    ->placeholder('N/A')
                    ->toggleable(),

                Columns\TextColumn::make('colaborador.nome')
                    ->label('Colaborador')
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => $record?->colaborador?->cargo?->cargo ?? '')
                    ->placeholder('N/A')
                    ->visible(function ($record) {
                        if (!$record || !$record->orcamento) {
                            return true;
                        }
                        
                        $orcamento = $record->orcamento;
                        
                        // Para nova rota, verificar se incluir_funcionario está marcado na tabela orcamento_proprio_nova_rota
                        if ($orcamento->tipo_orcamento === 'proprio_nova_rota') {
                            $proprioNovaRota = \App\Models\OrcamentoProprioNovaRota::where('orcamento_id', $orcamento->id)->first();
                            return $proprioNovaRota ? $proprioNovaRota->incluir_funcionario : false;
                        }
                        
                        // Para outros tipos, manter lógica original
                        return !in_array($orcamento->tipo_orcamento, ['prestador', 'aumento_km']);
                    }),

                Columns\TextColumn::make('veiculo.placa')
                    ->label('Veículo')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        // Primeiro tenta o veículo direto (novo campo)
                        if ($record?->veiculo) {
                            return $record->veiculo->placa;
                        }
                        // Fallback para frota (campo antigo)
                        if ($record?->frota?->tipoVeiculo) {
                            return $record->frota->tipoVeiculo->nome;
                        }
                        return null;
                    })
                    ->description(fn ($record) => 
                        $record?->veiculo 
                            ? $record->veiculo->marca_modelo 
                            : ($record?->frota?->fipe ?? '')
                    )
                    ->placeholder('N/A')
                    ->visible(function ($record) {
                        if (!$record || !$record->orcamento) {
                            return true;
                        }
                        
                        $orcamento = $record->orcamento;
                        
                        // Para nova rota, verificar se incluir_frota está marcado na tabela orcamento_proprio_nova_rota
                        if ($orcamento->tipo_orcamento === 'proprio_nova_rota') {
                            $proprioNovaRota = \App\Models\OrcamentoProprioNovaRota::where('orcamento_id', $orcamento->id)->first();
                            return $proprioNovaRota ? $proprioNovaRota->incluir_frota : false;
                        }
                        
                        // Para outros tipos, manter lógica original
                        return !in_array($orcamento->tipo_orcamento, ['prestador', 'aumento_km']);
                    }),

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
                    ->label('Valor Cobrado')
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(),

                Columns\TextColumn::make('custo_fornecedor')
                    ->label('Custo do Fornecedor')
                    ->state(function ($record) {
                        $orcamento = $record?->orcamento;
                        if (!$orcamento) {
                            return null;
                        }
                        
                        switch ($orcamento->tipo_orcamento) {
                            case 'prestador':
                                $prestador = $orcamento->prestadores()->select('custo_fornecedor')->first();
                                return $prestador?->custo_fornecedor;
                                
                            case 'proprio_nova_rota':
                                // Para nova rota, verificar se incluir_prestador está marcado
                                if (!$orcamento->incluir_prestador) {
                                    return null;
                                }
                                $proprioNovaRota = $orcamento->propriosNovaRota()->select('fornecedor_custo')->first();
                                return $proprioNovaRota?->fornecedor_custo;
                                
                            case 'aumento_km':
                                // Para aumento_km, pode não ter custo de fornecedor específico
                                return null;
                                
                            default:
                                return null;
                        }
                    })
                    ->money('BRL')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('orcamento_prestador', 'orcamento_prestador.orcamento_id', '=', 'obms.orcamento_id')
                            ->leftJoin('orcamento_proprio_nova_rota', 'orcamento_proprio_nova_rota.orcamento_id', '=', 'obms.orcamento_id')
                            ->orderByRaw("COALESCE(orcamento_prestador.custo_fornecedor, orcamento_proprio_nova_rota.fornecedor_custo) {$direction}")
                            ->select('obms.*');
                    })
                    ->toggleable()
                    ->placeholder('N/A'),

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

                Filter::make('custo_fornecedor')
                    ->label('Custo do Fornecedor')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min')
                            ->label('Mínimo')
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('max')
                            ->label('Máximo')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->leftJoin('orcamento_prestador', 'orcamento_prestador.orcamento_id', '=', 'obms.orcamento_id')
                            ->when(
                                $data['min'] ?? null,
                                fn (Builder $q, $min): Builder => $q->where('orcamento_prestador.custo_fornecedor', '>=', $min)
                            )
                            ->when(
                                $data['max'] ?? null,
                                fn (Builder $q, $max): Builder => $q->where('orcamento_prestador.custo_fornecedor', '<=', $max)
                            )
                            ->select('obms.*');
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Exportar Excel')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename(fn () => 'obms_' . now()->format('Ymd_His')),
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
