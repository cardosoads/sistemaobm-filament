<?php

namespace App\Filament\Resources\Obms\Schemas;

use App\Models\Orcamento;
use App\Models\Colaborador;
use App\Models\Frota;
use Filament\Forms\Components;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ObmForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('Dados da OBM')
                    ->columnSpanFull()
                    ->schema([
                        Components\Select::make('orcamento_id')
                            ->label('Orçamento')
                            ->placeholder('Selecione um orçamento aprovado')
                            ->relationship('orcamento', 'nome_rota', function (Builder $query) {
                                $query->where('status', 'aprovado')
                                      ->orderBy('nome_rota');
                            })
                            ->getOptionLabelFromRecordUsing(function (Orcamento $record) {
                                $clienteNome = $record->cliente?->nome ?? $record->cliente_nome ?? 'Cliente não informado';
                                $rota = $record->nome_rota ?? 'Rota sem nome';
                                $valor = is_numeric($record->valor_final) ? (float) $record->valor_final : 0;
                                return "{$rota} - {$clienteNome} - " . number_format($valor, 2, ',', '.');
                            })
                            ->searchable(['nome_rota', 'cliente_nome'])
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                // Limpar o campo de veículo quando o orçamento mudar
                                $set('frota_id', null);
                                // Dados do orçamento serão exibidos via relacionamento; não preencher campos na OBM.
                            })
                            ,
                    ]),

                SchemaComponents\Section::make('Execução da Rota')
                    ->columnSpanFull()
                    ->schema([
                        Components\DatePicker::make('data_inicio')
                            ->label('Data Início')
                            ->nullable()
                            ->native(false)
                            ->closeOnDateSelection()
                            ->minDate(now())
                            ->reactive(),

                        Components\DatePicker::make('data_fim')
                            ->label('Data Fim')
                            ->nullable()
                            ->native(false)
                            ->closeOnDateSelection()
                            ->minDate(fn (Get $get) => $get('data_inicio') ?: now())
                            ->reactive(),

                        Components\Select::make('colaborador_id')
                            ->label('Colaborador')
                            ->placeholder('Selecione o colaborador responsável')
                            ->relationship('colaborador', 'nome', function (Builder $query) {
                                $query->where('status', true)
                                      ->orderBy('nome');
                            })
                            ->getOptionLabelFromRecordUsing(fn (Colaborador $record) => 
                                "{$record->nome} - {$record->cargo->cargo} - {$record->base->base}"
                            )
                            ->searchable(['nome'])
                            ->preload()
                            ->required()
                            ->reactive()
                            ->helperText('O colaborador não pode ter sobreposição de datas com outras OBMs'),

                        Components\Select::make('veiculo_busca')
                            ->label('Buscar Veículo por Placa/RENAVAM')
                            ->placeholder('Digite placa ou RENAVAM para buscar')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\Veiculo::query()
                                    ->where('placa', 'like', "%{$search}%")
                                    ->orWhere('renavam', 'like', "%{$search}%")
                                    ->limit(10)
                                    ->get()
                                    ->mapWithKeys(fn ($veiculo) => [
                                        $veiculo->id => "Placa: {$veiculo->placa} - RENAVAM: {$veiculo->renavam} - " . 
                                                       "Modelo: {$veiculo->marca_modelo} - Tipo: " . 
                                                       ($veiculo->tipoVeiculo->nome ?? 'N/A')
                                    ]);
                            })
                            ->getOptionLabelUsing(fn ($value) => 
                                \App\Models\Veiculo::find($value)?->placa ?? $value
                            )
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state) {
                                    $veiculo = \App\Models\Veiculo::find($state);
                                    if ($veiculo && $veiculo->tipo_veiculo_id) {
                                        // Buscar frotas ativas do mesmo tipo de veículo
                                        $frota = \App\Models\Frota::where('tipo_veiculo_id', $veiculo->tipo_veiculo_id)
                                            ->where('active', true)
                                            ->first();
                                        if ($frota) {
                                            $set('frota_id', $frota->id);
                                        }
                                    }
                                }
                            })
                            ->helperText('Busque por placa ou RENAVAM para encontrar o veículo'),

                        Components\Select::make('frota_id')
                            ->label('Veículo da Frota')
                            ->placeholder('Selecione o veículo da frota')
                            ->relationship('frota', 'id', function (Builder $query, Get $get) {
                                $orcamentoId = $get('orcamento_id');
                                $veiculoBuscaId = $get('veiculo_busca');
                                
                                if ($veiculoBuscaId) {
                                    // Se há uma busca por veículo, filtrar por tipo de veículo
                                    $veiculo = \App\Models\Veiculo::find($veiculoBuscaId);
                                    if ($veiculo && $veiculo->tipo_veiculo_id) {
                                        $query->where('tipo_veiculo_id', $veiculo->tipo_veiculo_id);
                                    }
                                } elseif ($orcamentoId) {
                                    // Buscar o frota_id do orçamento selecionado
                                    $orcamento = \App\Models\Orcamento::find($orcamentoId);
                                    if ($orcamento && $orcamento->frota_id) {
                                        $query->where('id', $orcamento->frota_id);
                                    } else {
                                        // Se o orçamento não tem frota associada, não mostrar nenhum veículo
                                        $query->where('id', 0);
                                    }
                                } else {
                                    // Se não há orçamento selecionado, não mostrar nenhum veículo
                                    $query->where('id', 0);
                                }
                                
                                $query->where('active', true)
                                      ->with(['tipoVeiculo'])
                                      ->orderBy('tipo_veiculo_id');
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                "{$record->tipoVeiculo->nome} - {$record->fipe} - " . 
                                number_format($record->custo_total, 2, ',', '.')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->disabled(fn (Get $get) => !$get('orcamento_id') && !$get('veiculo_busca'))
                            ->helperText('Selecione um orçamento OU busque por placa/RENAVAM'),
                    ]),

                SchemaComponents\Section::make('Status e Observações')
                    ->columnSpanFull()
                    ->schema([
                        Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pendente' => 'Pendente',
                                'em_andamento' => 'Em Andamento',
                                'concluida' => 'Concluída',
                            ])
                            ->required()
                            ->default('pendente')
                            ->disabled(fn (Get $get) => !$get('colaborador_id') || !$get('frota_id'))
                            ->helperText('Status será bloqueado até selecionar colaborador e veículo'),

                        Components\Textarea::make('observacoes')
                            ->label('Observações')
                            ->placeholder('Informações adicionais sobre a execução da OBM')
                            ->rows(3),
                    ]),


            ]);
    }
}
