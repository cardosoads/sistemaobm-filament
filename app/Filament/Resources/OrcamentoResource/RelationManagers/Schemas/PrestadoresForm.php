<?php

namespace App\Filament\Resources\OrcamentoResource\RelationManagers\Schemas;

use Filament\Forms;
use Filament\Forms\Components;
use Filament\Forms\Components\Section;

class PrestadoresForm
{
    public static function schema(): array
    {
        return [
            Section::make('Dados do Prestador')
                ->schema([
                    Components\Select::make('fornecedor_omie_id')
                        ->label('Fornecedor')
                        ->relationship(
                            name: 'fornecedorOmie',
                            titleAttribute: 'razao_social',
                            modifyQueryUsing: fn ($query) => $query->where('ativo', true)
                        )
                        ->getOptionLabelFromRecordUsing(function ($record) {
                            return $record->codigo_cliente_omie . ' - ' . $record->razao_social . 
                                   ' (' . $record->nome_fantasia . ')';
                        })
                        ->searchable(['razao_social', 'nome_fantasia', 'codigo_cliente_omie'])
                        ->preload()
                        ->required()
                        ->helperText('Busque fornecedores por código, razão social ou nome fantasia'),

                    Components\TextInput::make('valor_referencia')
                        ->label('Valor de Referência')
                        ->numeric()
                        ->prefix('R$')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Components\Get $get, Components\Set $set) {
                            self::calcularValores($get, $set);
                        }),

                    Components\TextInput::make('qtd_dias')
                        ->label('Quantidade de Dias')
                        ->numeric()
                        ->required()
                        ->default(1)
                        ->live()
                        ->afterStateUpdated(function (Components\Get $get, Components\Set $set) {
                            self::calcularValores($get, $set);
                        })
                        ->helperText('Quantidade de dias de atendimento'),
                            
                    Components\TextInput::make('lucro_percentual')
                        ->label('Lucro (%)')
                        ->numeric()
                        ->suffix('%')
                        ->required()
                        ->default(20)
                        ->live()
                        ->afterStateUpdated(function (Components\Get $get, Components\Set $set) {
                            self::calcularValores($get, $set);
                        }),
                            
                    Components\Select::make('grupo_imposto_id')
                        ->label('Grupo de Imposto')
                        ->relationship('grupoImposto', 'nome')
                        ->getOptionLabelFromRecordUsing(fn (\App\Models\GrupoImposto $record) => 
                            $record->nome . ' (' . $record->percentual_total_formatado . ')'
                        )
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Components\Get $get, Components\Set $set) {
                            self::calcularValores($get, $set);
                        }),
                ])
                ->columns(2),
            // Seção de cálculos
            Section::make('Resumo dos Cálculos')
                ->schema([
                    Components\TextInput::make('custo_fornecedor_display')
                        ->label('Custo Fornecedor')
                        ->disabled()
                        ->dehydrated(false)
                        ->default('R$ 0,00'),

                    Components\TextInput::make('valor_lucro_display')
                        ->label('Valor Lucro')
                        ->disabled()
                        ->dehydrated(false)
                        ->default('R$ 0,00'),

                    Components\TextInput::make('valor_impostos_display')
                        ->label('Valor Impostos')
                        ->disabled()
                        ->dehydrated(false)
                        ->default('R$ 0,00'),

                    Components\TextInput::make('valor_total_display')
                        ->label('Valor Total')
                        ->disabled()
                        ->dehydrated(false)
                        ->default('R$ 0,00'),
                        
                    // Campos ocultos para armazenar os valores calculados
                    Components\Hidden::make('custo_fornecedor'),
                    Components\Hidden::make('valor_lucro'),
                    Components\Hidden::make('valor_impostos'),
                    Components\Hidden::make('valor_total'),
                ])
                ->columns(2)
                ->hiddenOn('edit'),
        ];
    }

    /**
     * Calcula todos os valores baseados nos inputs do usuário
     */
    public static function calcularValores(Components\Get $get, Components\Set $set): void
    {
        $valorReferencia = (float) ($get('valor_referencia') ?? 0);
        $qtdDias = (int) ($get('qtd_dias') ?? 1);
        $lucroPercentual = (float) ($get('lucro_percentual') ?? 0);
        $grupoImpostoId = $get('grupo_imposto_id');

        // Calcular custo fornecedor
        $custoFornecedor = $valorReferencia * $qtdDias;
        $set('custo_fornecedor', $custoFornecedor);
        $set('custo_fornecedor_display', 'R$ ' . number_format($custoFornecedor, 2, ',', '.'));

        // Calcular valor lucro
        $valorLucro = $custoFornecedor * ($lucroPercentual / 100);
        $set('valor_lucro', $valorLucro);
        $set('valor_lucro_display', 'R$ ' . number_format($valorLucro, 2, ',', '.'));

        // Calcular valor impostos
        $valorImpostos = 0;
        if ($grupoImpostoId) {
            $grupoImposto = \App\Models\GrupoImposto::find($grupoImpostoId);
            if ($grupoImposto) {
                $baseCalculo = $custoFornecedor + $valorLucro;
                $valorImpostos = $grupoImposto->calcularValorImpostos($baseCalculo);
            }
        }
        $set('valor_impostos', $valorImpostos);
        $set('valor_impostos_display', 'R$ ' . number_format($valorImpostos, 2, ',', '.'));

        // Calcular valor total
        $valorTotal = $custoFornecedor + $valorLucro + $valorImpostos;
        $set('valor_total', $valorTotal);
        $set('valor_total_display', 'R$ ' . number_format($valorTotal, 2, ',', '.'));
    }
}