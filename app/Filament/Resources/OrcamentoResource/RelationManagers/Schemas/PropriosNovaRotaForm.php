<?php

namespace App\Filament\Resources\OrcamentoResource\RelationManagers\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components;
use Filament\Forms\Get;

class PropriosNovaRotaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Seção de Configurações
                Components\Section::make('Configurações')
                    ->schema([
                        Components\Checkbox::make('incluir_funcionario')
                            ->label('Incluir Funcionário')
                            ->live()
                            ->default(false),
                            
                        Components\Checkbox::make('incluir_frota')
                            ->label('Incluir Frota')
                            ->live()
                            ->default(false),
                            
                        Components\Checkbox::make('incluir_fornecedor')
                            ->label('Incluir Fornecedor')
                            ->live()
                            ->default(false)
                            ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                if ($state) {
                                    // Buscar a frequência de atendimento do orçamento pai
                                    $orcamento = $livewire->getOwnerRecord();
                                    if ($orcamento && $orcamento->frequencia_atendimento) {
                                        $diasMarcados = is_array($orcamento->frequencia_atendimento) 
                                            ? count($orcamento->frequencia_atendimento) 
                                            : 0;
                                        $set('fornecedor_dias', $diasMarcados);
                                    }
                                    self::calcularValoresFornecedor($get, $set);
                                    self::calcularValoresGerais($get, $set);
                                } else {
                                    // Limpar valores do fornecedor quando desmarcado
                                    $set('fornecedor_omie_id', null);
                                    $set('fornecedor_referencia', null);
                                    $set('fornecedor_dias', null);
                                    $set('fornecedor_custo', 0);
                                    $set('fornecedor_lucro', 0);
                                    $set('fornecedor_impostos', 0);
                                    $set('fornecedor_total', 0);
                                    self::calcularValoresGerais($get, $set);
                                }
                            })
                    ]),
                    
                // Seção de Funcionários
                Components\Section::make('Funcionários')
                    ->schema([
                        Components\Select::make('base_id')
                            ->label('Base')
                            ->relationship('base', 'base')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                // Limpa o recurso humano quando a base muda
                                $set('recurso_humano_id', null);
                                $set('valor_funcionario', null);
                            }),
                            
                        Components\Select::make('recurso_humano_id')
                            ->label('Recurso Humano (Cargo)')
                            ->relationship(
                                'recursoHumano', 
                                'cargo',
                                fn ($query, $get) => $get('base_id') 
                                    ? $query->where('base_id', $get('base_id'))->where('active', true)
                                    : $query->where('id', 0) // Não mostra nenhum resultado se não há base selecionada
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->cargo} - {$record->tipo_contratacao}")
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Preenche automaticamente o valor do funcionário
                                if ($state) {
                                    $recursoHumano = \App\Models\RecursoHumano::find($state);
                                    if ($recursoHumano) {
                                        $set('valor_funcionario', $recursoHumano->custo_total_mao_obra);
                                        self::calcularValoresGerais($get, $set);
                                    }
                                } else {
                                    $set('valor_funcionario', null);
                                    self::calcularValoresGerais($get, $set);
                                }
                            })
                            ->disabled(fn ($get) => !$get('base_id'))
                            ->helperText('Primeiro selecione uma base para ver os recursos humanos disponíveis'),
                            
                        Components\TextInput::make('valor_funcionario')
                            ->label('Valor Funcionário')
                            ->numeric()
                            ->prefix('R$')
                            ->required()
                            ->default(0)
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Preenchido automaticamente ao selecionar um recurso humano')
                            ->live()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                self::calcularValoresGerais($get, $set);
                            })
                    ])
                    ->visible(fn ($get) => $get('incluir_funcionario')),
                    
                // Seção de Frota
                Components\Section::make('Frota')
                    ->schema([
                        Components\Select::make('frota_id')
                            ->label('Frota')
                            ->relationship('frota', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "Frota #{$record->id} - " . ($record->tipoVeiculo->codigo ?? 'N/A'))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state) {
                                    $frota = \App\Models\Frota::find($state);
                                    if ($frota) {
                                        $set('valor_aluguel_frota', $frota->aluguel_carro);
                                        self::calcularValoresGerais($get, $set);
                                    }
                                } else {
                                    $set('valor_aluguel_frota', null);
                                    self::calcularValoresGerais($get, $set);
                                }
                            }),
                            
                        Components\TextInput::make('valor_aluguel_frota')
                            ->label('Valor Aluguel da Frota')
                            ->numeric()
                            ->prefix('R$')
                            ->nullable()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Preenchido automaticamente ao selecionar uma frota')
                            ->live()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                self::calcularValoresGerais($get, $set);
                            }),
                            
                        Components\TextInput::make('quantidade_dias')
                            ->label('Quantidade de Dias')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->nullable()
                            ->live()

                            ->afterStateUpdated(function ($state, $get, $set) {
                                self::calcularValoresGerais($get, $set);
                            }),
                            
                        Components\TextInput::make('valor_combustivel')
                            ->label('Valor Combustível')
                            ->numeric()
                            ->prefix('R$')
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                self::calcularValoresGerais($get, $set);
                            }),
                            
                        Components\TextInput::make('valor_pedagio')
                            ->label('Valor Pedágio')
                            ->numeric()
                            ->prefix('R$')
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                self::calcularValoresGerais($get, $set);
                            })
                    ])
                    ->visible(function (Get $get) {
                        $incluirFrota = $get('incluir_frota');
                        \Log::info('DEBUG_ROTA: Valor de incluir_frota na função visible: ' . ($incluirFrota ? 'true' : 'false'));
                        return $incluirFrota;
                    }),
                    
                // Seção de Fornecedor
                Components\Section::make('Fornecedor')
                    ->schema([
                        Components\Select::make('fornecedor_omie_id')
                            ->label('Fornecedor')
                            ->relationship(
                                name: 'fornecedor',
                                titleAttribute: 'razao_social',
                                modifyQueryUsing: fn ($query) => $query
                                    ->where('is_cliente', false)
                                    ->where('inativo', 'N')
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                "{$record->codigo_cliente_omie} - {$record->razao_social}" . 
                                ($record->nome_fantasia ? " ({$record->nome_fantasia})" : "")
                            )
                            ->searchable(['razao_social', 'nome_fantasia', 'codigo_cliente_omie'])
                            ->placeholder('Buscar por código ou nome do fornecedor')
                            ->helperText('Busca fornecedores por código, razão social ou nome fantasia')
                            ->preload()
                            ->nullable(),
                            
                        Components\TextInput::make('fornecedor_referencia')
                            ->label('Valor de Referência')
                            ->numeric()
                            ->prefix('R$')
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                self::calcularValoresFornecedor($get, $set);
                            }),
                            
                        Components\TextInput::make('fornecedor_dias')
                            ->label('Quantidade de Dias')
                            ->numeric()
                            ->readOnly()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Preenchido automaticamente com base na Frequência de Atendimento')
                            ->live()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                self::calcularValoresFornecedor($get, $set);
                                self::calcularValoresGerais($get, $set);
                            })
                    ])
                    ->visible(fn ($get) => $get('incluir_fornecedor')),
                    
                // Seção de Valores
                Components\Section::make('Valores')
                    ->schema([
                        Components\TextInput::make('lucro_percentual')
                            ->label('Percentual de Lucro (%)')
                            ->numeric()
                            ->suffix('%')
                            ->required()
                            ->default(20)
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                self::calcularValoresFornecedor($get, $set);
                                self::calcularValoresGerais($get, $set);
                            }),
                            
                        Components\Select::make('grupo_imposto_id')
                            ->label('Grupo de Imposto')
                            ->relationship('grupoImposto', 'nome')
                            ->getOptionLabelFromRecordUsing(fn (\App\Models\GrupoImposto $record) => 
                                $record->nome . ' (' . $record->percentual_total_formatado . ')'
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('Selecione uma opção')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                self::calcularValoresFornecedor($get, $set);
                                self::calcularValoresGerais($get, $set);
                            }),
                            
                        // Valores Calculados (sempre visíveis)
                        Components\TextInput::make('valor_total_rotas')
                            ->label('Valor Total Rotas')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                            
                        Components\TextInput::make('fornecedor_custo')
                            ->label('Custo Fornecedor')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                            
                        Components\TextInput::make('fornecedor_lucro')
                            ->label('Lucro Fornecedor')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                            
                        Components\TextInput::make('fornecedor_impostos')
                            ->label('Impostos Fornecedor')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                            
                        Components\TextInput::make('fornecedor_total')
                            ->label('Total Fornecedor')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                            
                        Components\TextInput::make('valor_total_geral')
                            ->label('Valor Total Geral')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                            
                        Components\TextInput::make('valor_lucro')
                            ->label('Valor do Lucro')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                            
                        Components\TextInput::make('valor_impostos')
                            ->label('Valor dos Impostos')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                            
                        Components\TextInput::make('valor_final')
                            ->label('Valor Final')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated()
                            ->default(0)
                    ])
            ]);
    }

    /**
     * Calcula os valores específicos do fornecedor
     */
    public static function calcularValoresFornecedor($get, $set): void
    {
        \Log::info('calcularValoresFornecedor chamado');
        
        $fornecedorReferencia = (float) ($get('fornecedor_referencia') ?? 0);
        $fornecedorDias = (int) ($get('fornecedor_dias') ?? 0);
        $lucroPercentual = (float) ($get('lucro_percentual') ?? 0);
        $grupoImpostoId = $get('grupo_imposto_id');
        
        \Log::info('Valores recebidos:', [
            'fornecedor_referencia' => $fornecedorReferencia,
            'fornecedor_dias' => $fornecedorDias,
            'lucro_percentual' => $lucroPercentual,
            'grupo_imposto_id' => $grupoImpostoId
        ]);

        // Cálculo do custo do fornecedor
        $fornecedorCusto = $fornecedorReferencia * $fornecedorDias;
        $set('fornecedor_custo', $fornecedorCusto);

        // Cálculo do lucro do fornecedor
        $fornecedorLucro = $fornecedorCusto * ($lucroPercentual / 100);
        $set('fornecedor_lucro', $fornecedorLucro);

        // Cálculo dos impostos do fornecedor
        $fornecedorImpostos = 0;
        if ($grupoImpostoId) {
            $grupoImposto = \App\Models\GrupoImposto::find($grupoImpostoId);
            if ($grupoImposto) {
                $baseImpostosFornecedor = $fornecedorCusto + $fornecedorLucro;
                $fornecedorImpostos = $grupoImposto->calcularValorImpostos($baseImpostosFornecedor);
            }
        }
        $set('fornecedor_impostos', $fornecedorImpostos);

        // Valor total do fornecedor
        $fornecedorTotal = $fornecedorCusto + $fornecedorLucro + $fornecedorImpostos;
        $set('fornecedor_total', $fornecedorTotal);
    }

    /**
     * Calcula os valores gerais (total geral, lucro geral, impostos gerais, valor final)
     */
    public static function calcularValoresGerais($get, $set): void
    {
        \Log::info('calcularValoresGerais chamado');
        
        \Log::debug('DEBUG_ROTA: calcularValoresGerais method called');
        $valorFuncionario = (float) ($get('valor_funcionario') ?? 0);
        $valorAluguelFrota = (float) ($get('valor_aluguel_frota') ?? 0);
        $valorCombustivel = (float) ($get('valor_combustivel') ?? 0);
        $valorPedagio = (float) ($get('valor_pedagio') ?? 0);
        $fornecedorTotal = (float) ($get('fornecedor_total') ?? 0);
        $lucroPercentual = (float) ($get('lucro_percentual') ?? 0);
        $grupoImpostoId = $get('grupo_imposto_id');
        
        \Log::info('DEBUG_ROTA: Valores gerais recebidos:', [
            'valor_funcionario' => $valorFuncionario,
            'quantidade_dias' => $get('quantidade_dias'),
            'valor_combustivel_input' => $get('valor_combustivel'),
            'valor_pedagio_input' => $get('valor_pedagio'),
            'valor_aluguel_frota' => $valorAluguelFrota,
            'valor_combustivel_calculado' => $valorCombustivel,
            'valor_pedagio_calculado' => $valorPedagio,
            'fornecedor_total' => $fornecedorTotal,
            'lucro_percentual' => $lucroPercentual,
            'grupo_imposto_id' => $grupoImpostoId
        ]);

        // Valor total geral (funcionário + frota + combustível + pedágio + fornecedor)
        $valorTotalGeral = $valorFuncionario + $valorAluguelFrota + $valorCombustivel + $valorPedagio + $fornecedorTotal;
        $set('valor_total_geral', number_format($valorTotalGeral, 2, '.', ''));

        // Cálculo do lucro geral (sobre o total)
        $valorLucro = $valorTotalGeral * ($lucroPercentual / 100);
        $set('valor_lucro', number_format($valorLucro, 2, '.', ''));

        // Cálculo dos impostos gerais
        $valorImpostos = 0;
        if ($grupoImpostoId) {
            $grupoImposto = \App\Models\GrupoImposto::find($grupoImpostoId);
            if ($grupoImposto) {
                $baseImpostos = $valorTotalGeral + $valorLucro;
                $valorImpostos = $grupoImposto->calcularValorImpostos($baseImpostos);
            }
        }
        $set('valor_impostos', number_format($valorImpostos, 2, '.', ''));

        // Valor final (total + lucro + impostos)
        $valorFinal = $valorTotalGeral + $valorLucro + $valorImpostos;
        $set('valor_final', number_format($valorFinal, 2, '.', ''));
    }
}