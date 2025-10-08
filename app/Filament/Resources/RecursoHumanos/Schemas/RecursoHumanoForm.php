<?php

namespace App\Filament\Resources\RecursoHumanos\Schemas;

use App\Models\Base;
use App\Models\RecursoHumano;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RecursoHumanoForm
{
    /**
     * Calcula o custo total de mão de obra
     */
    private static function calcularCustoTotal($get): float
    {
        $salarioBase = (float) ($get('salario_base') ?? 0);
        $insalubridade = (float) ($get('insalubridade') ?? 0);
        $periculosidade = (float) ($get('periculosidade') ?? 0);
        $horasExtras = (float) ($get('horas_extras') ?? 0);
        $adicionalNoturno = (float) ($get('adicional_noturno') ?? 0);
        $extras = (float) ($get('extras') ?? 0);
        $valeTransporte = (float) ($get('vale_transporte') ?? 0);
        $beneficios = (float) ($get('beneficios') ?? 0);
        $encargosSociais = (float) ($get('encargos_sociais') ?? 0);

        return $salarioBase + $insalubridade + $periculosidade + $horasExtras + 
               $adicionalNoturno + $extras + $valeTransporte + $beneficios + $encargosSociais;
    }

    /**
     * Atualiza o custo total
     */
    private static function atualizarCustoTotal($set, $get): void
    {
        $custoTotal = self::calcularCustoTotal($get);
        $set('custo_total_mao_obra', number_format($custoTotal, 2, '.', ''));
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tipo_contratacao')
                    ->label('Tipo de Contratação')
                    ->options(RecursoHumano::getTiposContratacao())
                    ->required()
                    ->columnSpan(1),

                TextInput::make('cargo')
                    ->label('Cargo')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Digite o cargo')
                    ->columnSpan(1),

                Select::make('base_id')
                    ->label('Base Operacional')
                    ->options(Base::ativas()->orderBy('base')->pluck('base', 'id'))
                    ->searchable()
                    ->nullable()
                    ->columnSpan(2),

                TextInput::make('salario_base')
                    ->label('Salário Base')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCustoTotal($set, $get);
                    }),

                TextInput::make('insalubridade')
                    ->label('Insalubridade/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCustoTotal($set, $get);
                    })
                    ->columnSpan(1),

                TextInput::make('periculosidade')
                    ->label('Periculosidade/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCustoTotal($set, $get);
                    })
                    ->columnSpan(1),

                TextInput::make('horas_extras')
                    ->label('Horas Extras/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCustoTotal($set, $get);
                    })
                    ->columnSpan(1),

                TextInput::make('adicional_noturno')
                    ->label('Adicional Noturno/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCustoTotal($set, $get);
                    })
                    ->columnSpan(1),

                TextInput::make('extras')
                    ->label('Extras/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCustoTotal($set, $get);
                    })
                    ->columnSpan(1),

                TextInput::make('vale_transporte')
                    ->label('Vale Transporte/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCustoTotal($set, $get);
                    })
                    ->columnSpan(1),

                TextInput::make('beneficios')
                    ->label('Benefícios/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCustoTotal($set, $get);
                    })
                    ->columnSpan(1),

                TextInput::make('percentual_encargos')
                    ->label('Percentual Encargos (%)')
                    ->numeric()
                    ->inputMode('decimal')
                    ->suffix('%')
                    ->step(0.01)
                    ->columnSpan(1),

                TextInput::make('encargos_sociais')
                    ->label('Encargos Sociais/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCustoTotal($set, $get);
                    })
                    ->columnSpan(1),

                TextInput::make('custo_total_mao_obra')
                    ->label('Custo Total de Mão de Obra/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->disabled()
                    ->dehydrated()
                    ->afterStateHydrated(function ($set, $get) {
                        self::atualizarCustoTotal($set, $get);
                    })
                    ->helperText('Calculado automaticamente somando todos os campos acima')
                    ->columnSpan(2),

                Toggle::make('active')
                    ->label('Ativo')
                    ->default(true)
                    ->columnSpan(2),
            ])
            ->columns(2);
    }
}
