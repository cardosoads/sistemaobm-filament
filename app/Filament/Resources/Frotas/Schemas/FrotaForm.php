<?php

namespace App\Filament\Resources\Frotas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Models\TipoVeiculo;

class FrotaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tipo_veiculo_id')
                    ->label('Tipo de Veículo')
                    ->options(TipoVeiculo::pluck('codigo', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),

                Toggle::make('active')
                    ->label('Ativo')
                    ->default(true),

                TextInput::make('fipe')
                    ->label('Valor FIPE')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $percentualAluguel = $get('percentual_aluguel');
                        if ($percentualAluguel > 0 && $state) {
                            $aluguel = $state * ($percentualAluguel / 100);
                            $set('aluguel_carro', number_format($aluguel, 2, '.', ''));
                            
                            // Recalcular provisões de avarias se percentual estiver definido
                            $percentualProvisoes = $get('percentual_provisoes_avarias');
                            if ($percentualProvisoes > 0) {
                                $provisoes = $aluguel * ($percentualProvisoes / 100);
                                $set('provisoes_avarias', number_format($provisoes, 2, '.', ''));
                                
                                // Recalcular provisão de desmobilização se percentual estiver definido
                                $percentualDesmobilizacao = $get('percentual_provisao_desmobilizacao');
                                if ($percentualDesmobilizacao > 0) {
                                    $provisaoDesmobilizacao = $provisoes * ($percentualDesmobilizacao / 100);
                                    $set('provisao_desmobilizacao', number_format($provisaoDesmobilizacao, 2, '.', ''));
                                }
                            }
                            
                            // Recalcular provisão diária RAC se percentual estiver definido
                            $percentualRac = $get('percentual_provisao_rac');
                            if ($percentualRac > 0) {
                                $provisaoRac = $aluguel * ($percentualRac / 100);
                                $set('provisao_diaria_rac', number_format($provisaoRac, 2, '.', ''));
                            }
                            
                            // Atualizar custo total
                            self::atualizarCustoTotal($set, $get);
                        }
                    }),

                TextInput::make('percentual_aluguel')
                    ->label('Percentual Aluguel (%)')
                    ->numeric()
                    ->inputMode('decimal')
                    ->suffix('%')
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100)
                    ->helperText('Percentual sobre o valor FIPE para calcular o aluguel automaticamente')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        if ($state > 0 && $get('fipe')) {
                            $aluguel = $get('fipe') * ($state / 100);
                            $set('aluguel_carro', number_format($aluguel, 2, '.', ''));
                            
                            // Recalcular provisões de avarias se percentual estiver definido
                            $percentualProvisoes = $get('percentual_provisoes_avarias');
                            if ($percentualProvisoes > 0) {
                                $provisoes = $aluguel * ($percentualProvisoes / 100);
                                $set('provisoes_avarias', number_format($provisoes, 2, '.', ''));
                                
                                // Recalcular provisão de desmobilização se percentual estiver definido
                                $percentualDesmobilizacao = $get('percentual_provisao_desmobilizacao');
                                if ($percentualDesmobilizacao > 0) {
                                    $provisaoDesmobilizacao = $provisoes * ($percentualDesmobilizacao / 100);
                                    $set('provisao_desmobilizacao', number_format($provisaoDesmobilizacao, 2, '.', ''));
                                }
                            }
                            
                            // Recalcular provisão diária RAC se percentual estiver definido
                            $percentualRac = $get('percentual_provisao_rac');
                            if ($percentualRac > 0) {
                                $provisaoRac = $aluguel * ($percentualRac / 100);
                                $set('provisao_diaria_rac', number_format($provisaoRac, 2, '.', ''));
                            }
                            
                            // Atualizar custo total
                            self::atualizarCustoTotal($set, $get);
                        }
                    }),

                TextInput::make('aluguel_carro')
                    ->label('Aluguel do Carro')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->helperText('Será calculado automaticamente se o percentual de aluguel estiver definido')
                    ->disabled(fn ($get) => $get('percentual_aluguel') > 0)
                    ->required(fn ($get) => $get('percentual_aluguel') == 0)
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCustoTotal($set, $get);
                    }),

                TextInput::make('rastreador')
                    ->label('Rastreador')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCustoTotal($set, $get);
                    }),

                TextInput::make('percentual_provisoes_avarias')
                    ->label('Percentual Provisões Avarias (%)')
                    ->numeric()
                    ->inputMode('decimal')
                    ->suffix('%')
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100)
                    ->helperText('Percentual sobre o aluguel do carro para calcular as provisões automaticamente')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        if ($state > 0) {
                            $fipe = $get('fipe');
                            $percentualAluguel = $get('percentual_aluguel');
                            $aluguelCarro = $get('aluguel_carro');
                            
                            // Calcular aluguel se necessário
                            if ($percentualAluguel > 0 && $fipe) {
                                $aluguel = $fipe * ($percentualAluguel / 100);
                            } else {
                                $aluguel = $aluguelCarro ?? 0;
                            }
                            
                            // Calcular provisões de avarias
                            if ($aluguel > 0) {
                                $provisoes = $aluguel * ($state / 100);
                                $set('provisoes_avarias', number_format($provisoes, 2, '.', ''));
                                
                                // Recalcular provisão de desmobilização se percentual estiver definido
                                $percentualDesmobilizacao = $get('percentual_provisao_desmobilizacao');
                                if ($percentualDesmobilizacao > 0) {
                                    $provisaoDesmobilizacao = $provisoes * ($percentualDesmobilizacao / 100);
                                    $set('provisao_desmobilizacao', number_format($provisaoDesmobilizacao, 2, '.', ''));
                                }
                            }
                            
                            // Atualizar custo total
                            self::atualizarCustoTotal($set, $get);
                        }
                    }),

                TextInput::make('provisoes_avarias')
                    ->label('Valor das Provisões Avarias')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->helperText('Será calculado automaticamente se o percentual estiver definido')
                    ->disabled(fn ($get) => $get('percentual_provisoes_avarias') > 0)
                    ->required(fn ($get) => $get('percentual_provisoes_avarias') == 0)
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCustoTotal($set, $get);
                    }),

                TextInput::make('percentual_provisao_desmobilizacao')
                    ->label('Percentual Provisão Desmobilização (%)')
                    ->numeric()
                    ->inputMode('decimal')
                    ->suffix('%')
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100)
                    ->helperText('Percentual sobre as provisões de avarias para calcular a provisão de desmobilização')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        if ($state > 0) {
                            $provisoesAvarias = $get('provisoes_avarias');
                            if ($provisoesAvarias > 0) {
                                $provisaoDesmobilizacao = $provisoesAvarias * ($state / 100);
                                $set('provisao_desmobilizacao', number_format($provisaoDesmobilizacao, 2, '.', ''));
                            }
                        }
                        
                        // Atualizar custo total
                        self::atualizarCustoTotal($set, $get);
                    }),

                TextInput::make('provisao_desmobilizacao')
                    ->label('Provisão para Desmobilização')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->helperText('Será calculado automaticamente se o percentual estiver definido')
                    ->disabled(fn ($get) => $get('percentual_provisao_desmobilizacao') > 0)
                    ->required(fn ($get) => $get('percentual_provisao_desmobilizacao') == 0)
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCustoTotal($set, $get);
                    }),

                TextInput::make('percentual_provisao_rac')
                    ->label('Percentual Provisão RAC (%)')
                    ->numeric()
                    ->inputMode('decimal')
                    ->suffix('%')
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100)
                    ->helperText('Percentual sobre o aluguel do carro para calcular a provisão diária RAC')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        if ($state > 0) {
                            $aluguelCarro = $get('aluguel_carro');
                            if ($aluguelCarro > 0) {
                                $provisaoRac = $aluguelCarro * ($state / 100);
                                $set('provisao_diaria_rac', number_format($provisaoRac, 2, '.', ''));
                            }
                        }
                        
                        // Atualizar custo total
                        self::atualizarCustoTotal($set, $get);
                    }),

                TextInput::make('provisao_diaria_rac')
                    ->label('Valor da Provisão Diária RAC')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->helperText('Será calculado automaticamente se o percentual estiver definido')
                    ->disabled(fn ($get) => $get('percentual_provisao_rac') > 0)
                    ->required(fn ($get) => $get('percentual_provisao_rac') == 0)
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCustoTotal($set, $get);
                    }),

                TextInput::make('custo_total')
                    ->label('Custo Total')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->disabled()
                    ->dehydrated(),
            ]);
    }

    private static function calcularCustoTotal($get): float
    {
        $aluguelCarro = (float) ($get('aluguel_carro') ?? 0);
        $rastreador = (float) ($get('rastreador') ?? 0);
        $provisoesAvarias = (float) ($get('provisoes_avarias') ?? 0);
        $provisaoDesmobilizacao = (float) ($get('provisao_desmobilizacao') ?? 0);
        $provisaoDiariaRac = (float) ($get('provisao_diaria_rac') ?? 0);

        return $aluguelCarro + $rastreador + $provisoesAvarias + $provisaoDesmobilizacao + $provisaoDiariaRac;
    }

    private static function atualizarCustoTotal($set, $get): void
    {
        $custoTotal = self::calcularCustoTotal($get);
        $set('custo_total', number_format($custoTotal, 2, '.', ''));
    }
}
