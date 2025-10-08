<?php

namespace App\Filament\Resources\OrcamentoResource\Schemas;

use Filament\Forms\Components;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrcamentoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informações do Orçamento')
                    ->schema([
                        Components\Select::make('tipo_orcamento')
                            ->label('Tipo de Orçamento')
                            ->options([
                                'prestador' => 'Prestador',
                                'aumento_km' => 'Aumento de KM',
                                'proprio_nova_rota' => 'Próprio - Nova Rota',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                // Limpar campos quando o tipo mudar
                                $set('fornecedor_nome', null);
                                $set('valor_referencia', null);
                                $set('km_extra', null);
                                $set('valor_km_extra', null);
                                $set('origem', null);
                                $set('destino', null);
                                $set('km_rodado', null);
                                
                                // Se mudou para aumento_km, sincronizar quantidade_dias_aumento com frequencia
                                if ($state === 'aumento_km') {
                                    $frequenciaDias = $get('frequencia_atendimento_dias');
                                    $diasMarcados = is_array($frequenciaDias) ? count($frequenciaDias) : 0;
                                    $set('quantidade_dias_aumento', $diasMarcados);
                                }
                            }),

                        Components\DatePicker::make('data_solicitacao')
                            ->label('Data da Solicitação')
                            ->required()
                            ->default(now()),
                            
                        Components\Select::make('centro_custo_id')
                            ->label('Centro de Custo')
                            ->relationship('centroCusto', 'descricao')
                            ->searchable()
                            ->preload()
                            ->required(),
                            
                        Components\TextInput::make('id_protocolo')
                            ->label('ID de Protocolo')
                            ->maxLength(100)
                            ->nullable(),
                            
                        Components\Placeholder::make('numero_orcamento_display')
                            ->label('Número do Orçamento')
                            ->content('Será gerado automaticamente'),
                            
                        Components\TextInput::make('nome_rota')
                            ->label('Nome da Rota')
                            ->required()
                            ->maxLength(255),
                            
                        Components\TextInput::make('id_logcare')
                            ->label('ID LogCare')
                            ->numeric()
                            ->nullable(),
                            
                        Components\Select::make('cliente_omie')
                            ->label('Cliente (OMIE)')
                            ->placeholder('Selecione um cliente')
                            ->relationship(
                                name: 'clienteFornecedor',
                                titleAttribute: 'razao_social',
                                modifyQueryUsing: fn ($query) => $query->where('is_cliente', true)->where('inativo', 'N')
                            )
                            ->searchable(['razao_social', 'nome_fantasia', 'codigo_cliente_omie'])
                            ->preload()
                            ->nullable()
                            ->helperText('Busque por razão social, nome fantasia ou código OMIE'),
                            
                        Components\TimePicker::make('horario')
                            ->label('Horário')
                            ->nullable(),
                            
                        Components\CheckboxList::make('frequencia_atendimento_dias')
                            ->label('Frequência de Atendimento')
                            ->options([
                                'seg' => 'Seg',
                                'ter' => 'Ter',
                                'qua' => 'Qua',
                                'qui' => 'Qui',
                                'sex' => 'Sex',
                                'sab' => 'Sáb',
                                'dom' => 'Dom',
                            ])
                            ->columns(3)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                // Contar itens marcados e atualizar qtd_dias
                                $diasMarcados = is_array($state) ? count($state) : 0;
                                $set('qtd_dias', $diasMarcados);
                                
                                // Atualizar quantidade_dias_aumento para orçamentos do tipo "aumento_km"
                                if ($get('tipo_orcamento') === 'aumento_km') {
                                    $set('quantidade_dias_aumento', $diasMarcados);
                                }
                                
                                // Atualizar fornecedor_dias para orçamentos do tipo "proprio_nova_rota" quando incluir_prestador estiver marcado
                                if ($get('tipo_orcamento') === 'proprio_nova_rota' && $get('incluir_prestador')) {
                                    $set('fornecedor_dias', $diasMarcados);
                                }
                                
                                // Recalcular valores se for prestador
                                if ($get('tipo_orcamento') === 'prestador') {
                                    self::calcularValoresPrestador($get, $set);
                                }
                            })
                            ->nullable(),
                            
                        Components\Select::make('user_id')
                            ->label('Responsável')
                            ->relationship('user', 'name')
                            ->default(auth()->id())
                            ->required(),
                            
                        Components\DatePicker::make('data_orcamento')
                            ->label('Data do Orçamento')
                            ->default(now())
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                    
                // Seção condicional para Prestador
                Section::make('Dados do Prestador')
                    ->schema([
                        Components\Select::make('fornecedor_omie_id')
                            ->label('Fornecedor')
                            ->relationship(
                                name: 'fornecedor',
                                titleAttribute: 'razao_social',
                                modifyQueryUsing: fn ($query) => $query
                                    ->where('is_cliente', false)
                                    ->where('inativo', false)
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                "{$record->codigo_cliente_omie} - {$record->razao_social}" . 
                                ($record->nome_fantasia ? " ({$record->nome_fantasia})" : "")
                            )
                            ->searchable(['razao_social', 'nome_fantasia', 'codigo_cliente_omie'])
                            ->placeholder('Buscar por código ou nome do fornecedor')
                            ->helperText('Busca fornecedores por código, razão social ou nome fantasia')

                            ->nullable(),
                            

                            
                        Components\TextInput::make('valor_referencia')
                            ->label('Valor de Referência')
                            ->numeric()
                            ->prefix('R$')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calcularValoresPrestador($get, $set);
                            })
                            ->nullable(),
                            
                        Components\TextInput::make('qtd_dias')
                            ->label('Quantidade de Dias')
                            ->numeric()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calcularValoresPrestador($get, $set);
                            })
                            ->nullable(),
                            
                        Components\TextInput::make('lucro_percentual')
                            ->label('Lucro (%)')
                            ->numeric()
                            ->suffix('%')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calcularValoresPrestador($get, $set);
                            })
                            ->nullable(),
                            
                        Components\TextInput::make('impostos_percentual')
                            ->label('Impostos (%)')
                            ->numeric()
                            ->suffix('%')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calcularValoresPrestador($get, $set);
                            })
                            ->nullable(),
                            
                        Components\Select::make('grupo_imposto_id')
                            ->label('Grupo de Imposto')
                            ->relationship('grupoImposto', 'nome')
                            ->getOptionLabelFromRecordUsing(fn (\App\Models\GrupoImposto $record) => 
                                $record->nome . ' (' . $record->percentual_total_formatado . ')'
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calcularValoresPrestador($get, $set);
                            })
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn ($get) => $get('tipo_orcamento') === 'prestador'),
                    
                // Seção condicional para Aumento de KM
                Section::make('Dados do Aumento de KM')
                    ->schema([
                        Components\TextInput::make('km_por_dia')
                            ->label('KM por Dia')
                            ->numeric()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calcularValoresAumentoKm($get, $set);
                            })
                            ->nullable(),
                            
                        Components\TextInput::make('quantidade_dias_aumento')
                            ->label('Quantidade de Dias de Aumento')
                            ->numeric()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calcularValoresAumentoKm($get, $set);
                            })
                            ->nullable(),
                            
                        Components\TextInput::make('combustivel_km_litro')
                            ->label('Combustível KM/Litro')
                            ->numeric()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calcularValoresAumentoKm($get, $set);
                            })
                            ->nullable(),
                            
                        Components\TextInput::make('valor_combustivel')
                            ->label('Valor Combustível')
                            ->numeric()
                            ->prefix('R$')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calcularValoresAumentoKm($get, $set);
                            })
                            ->nullable(),
                            
                        Components\TextInput::make('hora_extra')
                            ->label('Hora Extra')
                            ->numeric()
                            ->prefix('R$')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calcularValoresAumentoKm($get, $set);
                            })
                            ->nullable(),
                            
                        Components\TextInput::make('pedagio')
                            ->label('Pedágio')
                            ->numeric()
                            ->prefix('R$')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calcularValoresAumentoKm($get, $set);
                            })
                            ->nullable(),
                            
                        Components\TextInput::make('percentual_lucro')
                            ->label('Percentual de Lucro (%)')
                            ->numeric()
                            ->suffix('%')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calcularValoresAumentoKm($get, $set);
                            })
                            ->nullable(),
                            
                        Components\Select::make('grupo_imposto_id')
                            ->label('Grupo de Imposto')
                            ->relationship('grupoImposto', 'nome')
                            ->getOptionLabelFromRecordUsing(fn (\App\Models\GrupoImposto $record) => 
                                $record->nome . ' (' . $record->percentual_total_formatado . ')'
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calcularValoresAumentoKm($get, $set);
                            })
                            ->nullable(),
                            
                        Components\TextInput::make('percentual_impostos')
                            ->label('Percentual de Impostos (%)')
                            ->numeric()
                            ->suffix('%')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calcularValoresAumentoKm($get, $set);
                            })
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn ($get) => $get('tipo_orcamento') === 'aumento_km'),
                    
                // Seção condicional para Próprio Nova Rota
                Section::make('Dados da Nova Rota')
                    ->schema([
                        // Checkboxes para controlar exibição de seções opcionais
                        Components\Checkbox::make('incluir_funcionario')
                            ->label('Incluir Funcionário')
                            ->live()
                            ->default(false),
                            
                        Components\Checkbox::make('incluir_frota')
                            ->label('Incluir Frota')
                            ->live()
                            ->default(false),
                            
                        Components\Checkbox::make('incluir_prestador')
                            ->label('Incluir Prestador')
                            ->live()
                            ->default(false)
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state && $get('tipo_orcamento') === 'proprio_nova_rota') {
                                    // Preenche automaticamente a quantidade de dias baseado na frequência de atendimento
                                    $frequenciaDias = $get('frequencia_atendimento_dias') ?? [];
                                    $quantidadeDias = count($frequenciaDias);
                                    $set('fornecedor_dias', $quantidadeDias);
                                }
                            }),
                            
                        // Campos de Funcionário (condicionais)
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
                            })
                            ->visible(fn ($get) => $get('incluir_funcionario')),
                            
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
                                    }
                                } else {
                                    $set('valor_funcionario', null);
                                }
                                // Recalcular valores se for tipo próprio nova rota
                                if ($get('tipo_orcamento') === 'proprio_nova_rota') {
                                    static::calcularValoresProprioNovaRota($get, $set);
                                }
                            })
                            ->disabled(fn ($get) => !$get('base_id'))
                            ->helperText('Primeiro selecione uma base para ver os recursos humanos disponíveis')
                            ->visible(fn ($get) => $get('incluir_funcionario')),
                            
                        Components\TextInput::make('valor_funcionario')
                            ->label('Valor do Funcionário')
                            ->numeric()
                            ->prefix('R$')
                            ->nullable()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Preenchido automaticamente ao selecionar um recurso humano')
                            ->visible(fn ($get) => $get('incluir_funcionario')),
                            
                        // Campos de Frota (condicionais)
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
                                    }
                                } else {
                                    $set('valor_aluguel_frota', null);
                                }
                                // Recalcular valores se for tipo próprio nova rota
                                if ($get('tipo_orcamento') === 'proprio_nova_rota') {
                                    static::calcularValoresProprioNovaRota($get, $set);
                                }
                            })
                            ->visible(fn ($get) => $get('incluir_frota')),
                            
                        Components\TextInput::make('valor_aluguel_frota')
                            ->label('Valor Aluguel da Frota')
                            ->numeric()
                            ->prefix('R$')
                            ->nullable()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Preenchido automaticamente ao selecionar uma frota')
                            ->visible(fn ($get) => $get('incluir_frota')),
                            
                        Components\TextInput::make('valor_combustivel')
                            ->label('Valor Combustível')
                            ->numeric()
                            ->prefix('R$')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                if ($get('tipo_orcamento') === 'proprio_nova_rota') {
                                    static::calcularValoresProprioNovaRota($get, $set);
                                }
                            })
                            ->nullable()
                            ->visible(fn ($get) => $get('incluir_frota')),
                            
                        Components\TextInput::make('valor_pedagio')
                            ->label('Valor Pedágio')
                            ->numeric()
                            ->prefix('R$')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                if ($get('tipo_orcamento') === 'proprio_nova_rota') {
                                    static::calcularValoresProprioNovaRota($get, $set);
                                }
                            })
                            ->nullable()
                            ->visible(fn ($get) => $get('incluir_frota')),
                            
                        // Campos de Prestador (condicionais)
                        Components\Select::make('fornecedor_omie_id_rota')
                            ->label('Fornecedor')
                            ->relationship(
                                name: 'fornecedorRota',
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
                            ->nullable()
                            ->visible(fn ($get) => $get('incluir_prestador')),
                            
                        Components\TextInput::make('fornecedor_referencia')
                            ->label('Valor de Referência')
                            ->numeric()
                            ->prefix('R$')
                            ->nullable()
                            ->visible(fn ($get) => $get('incluir_prestador')),
                            
                        Components\TextInput::make('fornecedor_dias')
                            ->label('Quantidade de Dias')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Preenchido automaticamente com base na Frequência de Atendimento')
                            ->visible(fn ($get) => $get('incluir_prestador')),
                            
                        Components\TextInput::make('lucro_percentual_rota')
                            ->label('Lucro (%)')
                            ->numeric()
                            ->suffix('%')
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Recalcular valores se for tipo próprio nova rota
                                if ($get('tipo_orcamento') === 'proprio_nova_rota') {
                                    static::calcularValoresProprioNovaRota($get, $set);
                                }
                            })
                            ->visible(fn ($get) => $get('incluir_prestador')),
                            
                        Components\TextInput::make('impostos_percentual_rota')
                            ->label('Impostos (%)')
                            ->numeric()
                            ->suffix('%')
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Recalcular valores se for tipo próprio nova rota
                                if ($get('tipo_orcamento') === 'proprio_nova_rota') {
                                    static::calcularValoresProprioNovaRota($get, $set);
                                }
                            })
                            ->visible(fn ($get) => $get('incluir_prestador')),
                            
                        Components\Select::make('grupo_imposto_id_rota')
                            ->label('Grupo de Imposto')
                            ->relationship('grupoImposto', 'nome')
                            ->getOptionLabelFromRecordUsing(fn (\App\Models\GrupoImposto $record) => 
                                $record->nome . ' (' . $record->percentual_total_formatado . ')'
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('Selecione uma opção')
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Recalcular valores se for tipo próprio nova rota
                                if ($get('tipo_orcamento') === 'proprio_nova_rota') {
                                    static::calcularValoresProprioNovaRota($get, $set);
                                }
                            })
                            ->visible(fn ($get) => $get('incluir_prestador')),
                    ])
                    ->columnSpanFull()
                    ->visible(fn ($get) => $get('tipo_orcamento') === 'proprio_nova_rota'),

                Section::make('Valores')
                    ->schema([
                        // Resumo detalhado para próprio nova rota
                        Components\Placeholder::make('resumo_calculo')
                            ->label('Resumo dos Cálculos')
                            ->content(function (Get $get) {
                                if ($get('tipo_orcamento') !== 'proprio_nova_rota') {
                                    return '';
                                }
                                
                                $valorFuncionario = (float) ($get('valor_funcionario') ?? 0);
                                $valorAluguelFrota = (float) ($get('valor_aluguel_frota') ?? 0);
                                $lucroPercentual = (float) ($get('lucro_percentual_rota') ?? 0);
                                $grupoImpostoId = $get('grupo_imposto_id_rota');
                                
                                // Calcular valores
                                $valorTotalGeral = $valorFuncionario + $valorAluguelFrota;
                                $valorLucro = $valorTotalGeral * ($lucroPercentual / 100);
                                $baseImpostos = $valorTotalGeral + $valorLucro;
                                
                                // Obter percentual de impostos
                                $percentualImpostos = 0;
                                $valorImpostos = 0;
                                if ($grupoImpostoId) {
                                    $grupoImposto = \App\Models\GrupoImposto::find($grupoImpostoId);
                                    if ($grupoImposto) {
                                        $percentualImpostos = $grupoImposto->percentual_total;
                                        $valorImpostos = $grupoImposto->calcularValorImpostos($baseImpostos);
                                    }
                                }
                                
                                return new \Illuminate\Support\HtmlString('
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="font-medium">Valor Total Geral:</span>
                                            <span>R$ ' . number_format($valorTotalGeral, 2, ',', '.') . '</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Valor Lucro (' . $lucroPercentual . '%):</span>
                                            <span>R$ ' . number_format($valorLucro, 2, ',', '.') . '</span>
                                        </div>
                                        <div class="flex justify-between border-t pt-2">
                                            <span class="font-medium">Base para Impostos (Total + Lucro):</span>
                                            <span class="font-semibold">R$ ' . number_format($baseImpostos, 2, ',', '.') . '</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Percentual Imposto:</span>
                                            <span>' . $percentualImpostos . '%</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Valor Impostos Calculado:</span>
                                            <span>R$ ' . number_format($valorImpostos, 2, ',', '.') . '</span>
                                        </div>
                                        <div class="flex justify-between border-t pt-2 text-lg font-bold">
                                            <span>Valor Final:</span>
                                            <span>R$ ' . number_format($baseImpostos + $valorImpostos, 2, ',', '.') . '</span>
                                        </div>
                                    </div>
                                ');
                            })
                            ->reactive()
                            ->live()
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('tipo_orcamento') === 'proprio_nova_rota'),
                            
                        Components\TextInput::make('valor_total')
                            ->label('Valor Total')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                            
                        Components\TextInput::make('valor_impostos')
                            ->label('Valor Impostos')
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
                            ->default(0),
                            
                        // Campo hidden para trigger de atualização do resumo
                        Components\Hidden::make('resumo_trigger')
                            ->dehydrated(false),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Status')
                    ->schema([
                        Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'em_andamento' => 'Em Andamento',
                                'enviado' => 'Enviado',
                                'aprovado' => 'Aprovado',
                                'rejeitado' => 'Rejeitado',
                                'cancelado' => 'Cancelado',
                            ])
                            ->default('em_andamento')
                            ->required(),
                            
                        Components\Textarea::make('observacoes')
                            ->label('Observações')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Calcula todos os valores baseados nos inputs do usuário para orçamento de prestador
     */
    public static function calcularValoresPrestador(Get $get, Set $set): void
    {
        // Só calcular se for tipo prestador
        if ($get('tipo_orcamento') !== 'prestador') {
            return;
        }

        $valorReferencia = (float) ($get('valor_referencia') ?? 0);
        $qtdDias = (int) ($get('qtd_dias') ?? 1);
        $lucroPercentual = (float) ($get('lucro_percentual') ?? 0);
        $impostosPercentual = (float) ($get('impostos_percentual') ?? 0);
        $grupoImpostoId = $get('grupo_imposto_id');

        // Calcular custo fornecedor (valor base)
        $custoFornecedor = $valorReferencia * $qtdDias;

        // Calcular valor lucro
        $valorLucro = $custoFornecedor * ($lucroPercentual / 100);

        // Calcular valor impostos (sobre custo + lucro)
        $baseImpostos = $custoFornecedor + $valorLucro;
        
        // Usar o grupo de impostos se disponível, senão usar o percentual manual
        $valorImpostos = 0;
        if ($grupoImpostoId) {
            $grupoImposto = \App\Models\GrupoImposto::find($grupoImpostoId);
            if ($grupoImposto) {
                $valorImpostos = $grupoImposto->calcularValorImpostos($baseImpostos);
            }
        } else {
            // Fallback para o campo impostos_percentual se não houver grupo
            $valorImpostos = $baseImpostos * ($impostosPercentual / 100);
        }

        // Calcular valor total
        $valorTotal = $baseImpostos + $valorImpostos;

        // Definir os valores calculados
        $set('valor_impostos', $valorImpostos);
        $set('valor_total', $valorTotal);
        $set('valor_final', $valorTotal);
    }

    /**
     * Calcula todos os valores baseados nos inputs do usuário para orçamento de aumento de KM
     */
    public static function calcularValoresAumentoKm(Get $get, Set $set): void
    {
        // Só calcular se for tipo aumento_km
        if ($get('tipo_orcamento') !== 'aumento_km') {
            return;
        }

        $kmPorDia = (float) ($get('km_por_dia') ?? 0);
        $quantidadeDiasAumento = (int) ($get('quantidade_dias_aumento') ?? 0);
        $combustivelKmLitro = (float) ($get('combustivel_km_litro') ?? 1); // Evitar divisão por zero
        $valorCombustivel = (float) ($get('valor_combustivel') ?? 0);
        $horaExtra = (float) ($get('hora_extra') ?? 0);
        $pedagio = (float) ($get('pedagio') ?? 0);
        $percentualLucro = (float) ($get('percentual_lucro') ?? 0);
        $percentualImpostos = (float) ($get('percentual_impostos') ?? 0);
        $grupoImpostoId = $get('grupo_imposto_id');

        // Cálculo do valor total base
        $kmTotal = $kmPorDia * $quantidadeDiasAumento;
        $litrosNecessarios = $combustivelKmLitro > 0 ? $kmTotal / $combustivelKmLitro : 0;
        $custoCombustivel = $litrosNecessarios * $valorCombustivel;
        
        $valorTotalBase = $custoCombustivel + $horaExtra + $pedagio;

        // Cálculo do lucro
        $valorLucro = $valorTotalBase * ($percentualLucro / 100);

        // Cálculo dos impostos (sobre total + lucro)
        $baseImpostos = $valorTotalBase + $valorLucro;
        
        // Usar o grupo de impostos se disponível, senão usar o percentual manual
        $valorImpostos = 0;
        if ($grupoImpostoId) {
            $grupoImposto = \App\Models\GrupoImposto::find($grupoImpostoId);
            if ($grupoImposto) {
                $valorImpostos = $grupoImposto->calcularValorImpostos($baseImpostos);
                // Atualizar o percentual de impostos baseado no grupo
                $set('percentual_impostos', $grupoImposto->percentual_total);
            }
        } else {
            // Fallback para o campo percentual_impostos se não houver grupo
            $valorImpostos = $baseImpostos * ($percentualImpostos / 100);
        }

        // Valor final
        $valorFinal = $baseImpostos + $valorImpostos;

        // Definir os valores calculados
        $set('valor_total', $valorTotalBase);
        $set('valor_impostos', $valorImpostos);
        $set('valor_final', $valorFinal);
    }

    /**
     * Calcula todos os valores baseados nos inputs do usuário para orçamento próprio nova rota
     */
    public static function calcularValoresProprioNovaRota(Get $get, Set $set): void
    {
        // Só calcular se for tipo proprio_nova_rota
        if ($get('tipo_orcamento') !== 'proprio_nova_rota') {
            return;
        }

        $valorFuncionario = (float) ($get('valor_funcionario') ?? 0);
        $valorAluguelFrota = (float) ($get('valor_aluguel_frota') ?? 0);
        $valorCombustivel = (float) ($get('valor_combustivel') ?? 0);
        $valorPedagio = (float) ($get('valor_pedagio') ?? 0);
        $lucroPercentual = (float) ($get('lucro_percentual_rota') ?? 0);
        $impostosPercentual = (float) ($get('impostos_percentual_rota') ?? 0);
        $grupoImpostoId = $get('grupo_imposto_id_rota'); // Corrigido para usar o campo correto

        // Calcular valor total base (funcionário + aluguel frota + combustível + pedágio)
        $valorTotalBase = $valorFuncionario + $valorAluguelFrota + $valorCombustivel + $valorPedagio;

        // Calcular valor lucro
        $valorLucro = $valorTotalBase * ($lucroPercentual / 100);

        // Calcular valor impostos (sobre total + lucro)
        $baseImpostos = $valorTotalBase + $valorLucro;
        
        // Usar o grupo de impostos se disponível, senão usar o percentual manual
        $valorImpostos = 0;
        if ($grupoImpostoId) {
            $grupoImposto = \App\Models\GrupoImposto::find($grupoImpostoId);
            if ($grupoImposto) {
                $valorImpostos = $grupoImposto->calcularValorImpostos($baseImpostos);
                // Atualizar o percentual de impostos baseado no grupo
                $set('impostos_percentual_rota', $grupoImposto->percentual_total);
            }
        } else {
            // Fallback para o campo impostos_percentual_rota se não houver grupo
            $valorImpostos = $baseImpostos * ($impostosPercentual / 100);
        }

        // Valor final
        $valorFinal = $baseImpostos + $valorImpostos;

        // Definir os valores calculados
        $set('valor_total', $valorTotalBase);
        $set('valor_impostos', $valorImpostos);
        $set('valor_final', $valorFinal);

        // Trigger para forçar atualização do resumo
        $set('resumo_trigger', time());

        // Log para debug
        \Log::info('Calculando valores próprio nova rota', [
            'valor_funcionario' => $valorFuncionario,
            'valor_aluguel_frota' => $valorAluguelFrota,
            'valor_combustivel' => $valorCombustivel,
            'valor_pedagio' => $valorPedagio,
            'lucro_percentual' => $lucroPercentual,
            'grupo_imposto_id' => $grupoImpostoId,
            'valor_total_base' => $valorTotalBase,
            'valor_lucro' => $valorLucro,
            'valor_impostos' => $valorImpostos,
            'valor_final' => $valorFinal
        ]);
    }
}