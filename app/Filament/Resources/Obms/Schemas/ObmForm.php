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
                            ->disabled(fn () => auth()->user()?->hasAnyRole(['Recursos Humanos', 'Frotas']))
                            ->dehydrated(fn () => !auth()->user()?->hasAnyRole(['Recursos Humanos', 'Frotas']))
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                // Limpar o campo de veículo quando o orçamento mudar
                                $set('frota_id', null);
                                // Dados do orçamento serão exibidos via relacionamento; não preencher campos na OBM.
                            })
                            ,

                        Components\DatePicker::make('data_inicio')
                            ->label('Data Início')
                            ->nullable()
                            ->native(false)
                            ->closeOnDateSelection()
                            ->minDate(function () {
                                if (auth()->user()?->hasAnyRole(['Recursos Humanos', 'Frotas'])) {
                                    return null;
                                }
                                return now()->format('Y-m-d');
                            })
                            ->reactive()
                            ->disabled(fn () => auth()->user()?->hasAnyRole(['Recursos Humanos', 'Frotas']))
                            ->dehydrated(fn () => !auth()->user()?->hasAnyRole(['Recursos Humanos', 'Frotas'])),

                        Components\DatePicker::make('data_fim')
                            ->label('Data Fim')
                            ->nullable()
                            ->native(false)
                            ->closeOnDateSelection()
                            ->minDate(function (Get $get) {
                                if (auth()->user()?->hasAnyRole(['Recursos Humanos', 'Frotas'])) {
                                    return null;
                                }
                                
                                $dataInicio = $get('data_inicio');
                                if ($dataInicio) {
                                    return $dataInicio;
                                }
                                
                                return now()->format('Y-m-d');
                            })
                            ->reactive()
                            ->disabled(fn () => auth()->user()?->hasAnyRole(['Recursos Humanos', 'Frotas']))
                            ->dehydrated(fn () => !auth()->user()?->hasAnyRole(['Recursos Humanos', 'Frotas'])),

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
                            ->required(function (Get $get) {
                                // Para roles RH/Frotas, não exigir colaborador
                                if (auth()->user()?->hasAnyRole(['Recursos Humanos', 'Frotas'])) {
                                    return false;
                                }
                                $orcamentoId = $get('orcamento_id');
                                if (!$orcamentoId) return true;
                                
                                $orcamento = Orcamento::find($orcamentoId);
                                if (!$orcamento) return true;
                                
                                return !in_array($orcamento->tipo_orcamento, ['prestador', 'aumento_km']);
                            })
                            ->visible(function (Get $get) {
                                $orcamentoId = $get('orcamento_id');
                                if (!$orcamentoId) return true;
                                
                                $orcamento = Orcamento::find($orcamentoId);
                                if (!$orcamento) return true;
                                
                                // Para nova rota, verificar se incluir_funcionario está marcado na tabela orcamento_proprio_nova_rota
                                if ($orcamento->tipo_orcamento === 'proprio_nova_rota') {
                                    $proprioNovaRota = \App\Models\OrcamentoProprioNovaRota::where('orcamento_id', $orcamento->id)->first();
                                    return $proprioNovaRota ? $proprioNovaRota->incluir_funcionario : false;
                                }
                                
                                return !in_array($orcamento->tipo_orcamento, ['prestador', 'aumento_km']);
                            })
                            ->reactive()
                            ->disabled(fn () => auth()->user()?->hasRole('Frotas'))
                            ->dehydrated(fn () => !auth()->user()?->hasRole('Frotas'))
                            ->helperText('O colaborador não pode ter sobreposição de datas com outras OBMs'),

                        Components\Select::make('veiculo_id')
                            ->label('Veículo')
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
                            ->getOptionLabelUsing(function ($value) {
                                $veiculo = \App\Models\Veiculo::find($value);
                                if (!$veiculo) return $value;
                                return "Placa: {$veiculo->placa} - RENAVAM: {$veiculo->renavam} - " . 
                                       "Modelo: {$veiculo->marca_modelo} - Tipo: " . 
                                       ($veiculo->tipoVeiculo->nome ?? 'N/A');
                            })
                            ->reactive()
                            ->visible(function (Get $get) {
                                $orcamentoId = $get('orcamento_id');
                                if (!$orcamentoId) return true;
                                
                                $orcamento = Orcamento::find($orcamentoId);
                                if (!$orcamento) return true;
                                
                                // Para nova rota, verificar se incluir_frota está marcado na tabela orcamento_proprio_nova_rota
                                if ($orcamento->tipo_orcamento === 'proprio_nova_rota') {
                                    $proprioNovaRota = \App\Models\OrcamentoProprioNovaRota::where('orcamento_id', $orcamento->id)->first();
                                    return $proprioNovaRota ? $proprioNovaRota->incluir_frota : false;
                                }
                                
                                return !in_array($orcamento->tipo_orcamento, ['prestador', 'aumento_km']);
                            })
                            ->disabled(fn () => auth()->user()?->hasRole('Recursos Humanos'))
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


                        Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pendente' => 'Pendente',
                                'em_andamento' => 'Em Andamento',
                                'concluida' => 'Concluída',
                            ])
                            ->required()
                            ->default('pendente')
                            ->disabled(function (Get $get) {
                                if (auth()->user()?->hasAnyRole(['Recursos Humanos', 'Frotas'])) {
                                    return true;
                                }
                                $orcamentoId = $get('orcamento_id');
                                if (!$orcamentoId) return true;
                                
                                $orcamento = Orcamento::find($orcamentoId);
                                if (!$orcamento) return true;
                                
                                // Para prestador e aumento_km, não precisa de colaborador e veículo
                                if (in_array($orcamento->tipo_orcamento, ['prestador', 'aumento_km'])) {
                                    return false;
                                }
                                
                                // Para outros tipos, precisa de colaborador e veículo
                                return !$get('colaborador_id') || !$get('frota_id');
                            })
                            ->helperText(function (Get $get) {
                                $orcamentoId = $get('orcamento_id');
                                if (!$orcamentoId) return 'Selecione um orçamento primeiro';
                                
                                $orcamento = Orcamento::find($orcamentoId);
                                if (!$orcamento) return 'Orçamento não encontrado';
                                
                                if (in_array($orcamento->tipo_orcamento, ['prestador', 'aumento_km'])) {
                                    return 'Status disponível para orçamentos de prestador e aumento KM';
                                }
                                
                                return 'Status será bloqueado até selecionar colaborador e veículo';
                            }),

                        Components\Textarea::make('observacoes')
                            ->label('Observações')
                            ->placeholder('Informações adicionais sobre a execução da OBM')
                            ->rows(3)
                            ->disabled(fn () => auth()->user()?->hasAnyRole(['Recursos Humanos', 'Frotas']))
                            ->dehydrated(fn () => !auth()->user()?->hasAnyRole(['Recursos Humanos', 'Frotas']))

                    ])
             ]);
     }
 }
