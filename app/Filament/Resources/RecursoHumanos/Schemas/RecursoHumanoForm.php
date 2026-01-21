<?php

namespace App\Filament\Resources\RecursoHumanos\Schemas;

use App\Models\Base;
use App\Models\GrupoImposto;
use App\Models\RecursoHumano;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RecursoHumanoForm
{
    /**
     * Calcula o salário bruto (base + adicionais, sem encargos)
     */
    private static function calcularSalarioBruto($get): float
    {
        $salarioBase = (float) ($get('salario_base') ?? 0);
        $insalubridade = (float) ($get('insalubridade') ?? 0);
        $periculosidade = (float) ($get('periculosidade') ?? 0);
        $horasExtras = (float) ($get('horas_extras') ?? 0);
        $adicionalNoturno = (float) ($get('adicional_noturno') ?? 0);
        $extras = (float) ($get('extras') ?? 0);
        $valeTransporte = (float) ($get('vale_transporte') ?? 0);
        $beneficios = (float) ($get('beneficios') ?? 0);

        return $salarioBase + $insalubridade + $periculosidade + $horasExtras +
               $adicionalNoturno + $extras + $valeTransporte + $beneficios;
    }

    /**
     * Calcula os encargos sociais baseado no percentual do grupo
     */
    private static function calcularEncargosSociais($get): float
    {
        $salarioBruto = self::calcularSalarioBruto($get);
        $percentual = (float) ($get('percentual_encargos') ?? 0);

        return $salarioBruto * ($percentual / 100);
    }

    /**
     * Calcula o custo total de mão de obra
     */
    private static function calcularCustoTotal($get): float
    {
        $salarioBruto = self::calcularSalarioBruto($get);
        $encargosSociais = (float) ($get('encargos_sociais') ?? 0);

        return $salarioBruto + $encargosSociais;
    }

    /**
     * Atualiza encargos sociais e custo total
     */
    private static function atualizarCalculos($set, $get): void
    {
        $encargosSociais = self::calcularEncargosSociais($get);
        $set('encargos_sociais', number_format($encargosSociais, 2, '.', ''));

        // Recalcular custo total com os novos encargos
        $salarioBruto = self::calcularSalarioBruto($get);
        $custoTotal = $salarioBruto + $encargosSociais;
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
                        self::atualizarCalculos($set, $get);
                    }),

                TextInput::make('insalubridade')
                    ->label('Insalubridade/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCalculos($set, $get);
                    })
                    ->columnSpan(1),

                TextInput::make('periculosidade')
                    ->label('Periculosidade/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCalculos($set, $get);
                    })
                    ->columnSpan(1),

                TextInput::make('horas_extras')
                    ->label('Horas Extras/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCalculos($set, $get);
                    })
                    ->columnSpan(1),

                TextInput::make('adicional_noturno')
                    ->label('Adicional Noturno/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCalculos($set, $get);
                    })
                    ->columnSpan(1),

                TextInput::make('extras')
                    ->label('Extras/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCalculos($set, $get);
                    })
                    ->columnSpan(1),

                TextInput::make('vale_transporte')
                    ->label('Vale Transporte/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCalculos($set, $get);
                    })
                    ->columnSpan(1),

                TextInput::make('beneficios')
                    ->label('Benefícios/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        self::atualizarCalculos($set, $get);
                    })
                    ->columnSpan(1),

                Select::make('grupo_imposto_id')
                    ->label('Percentual Encargos (%)')
                    ->options(function () {
                        return GrupoImposto::ativos()
                            ->with('impostos')
                            ->get()
                            ->mapWithKeys(fn ($grupo) => [
                                $grupo->id => "{$grupo->nome} - {$grupo->percentual_total}%"
                            ]);
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        if ($state) {
                            $grupo = GrupoImposto::with('impostos')->find($state);
                            if ($grupo) {
                                $percentual = $grupo->percentual_total;
                                $set('percentual_encargos', $percentual);

                                // Calcular encargos sociais
                                $salarioBase = (float) ($get('salario_base') ?? 0);
                                $insalubridade = (float) ($get('insalubridade') ?? 0);
                                $periculosidade = (float) ($get('periculosidade') ?? 0);
                                $horasExtras = (float) ($get('horas_extras') ?? 0);
                                $adicionalNoturno = (float) ($get('adicional_noturno') ?? 0);
                                $extras = (float) ($get('extras') ?? 0);
                                $valeTransporte = (float) ($get('vale_transporte') ?? 0);
                                $beneficios = (float) ($get('beneficios') ?? 0);

                                $salarioBruto = $salarioBase + $insalubridade + $periculosidade + $horasExtras +
                                               $adicionalNoturno + $extras + $valeTransporte + $beneficios;

                                $encargosSociais = $salarioBruto * ($percentual / 100);
                                $set('encargos_sociais', number_format($encargosSociais, 2, '.', ''));

                                $custoTotal = $salarioBruto + $encargosSociais;
                                $set('custo_total_mao_obra', number_format($custoTotal, 2, '.', ''));
                            }
                        } else {
                            $set('percentual_encargos', 0);
                            $set('encargos_sociais', '0.00');

                            // Recalcular custo total sem encargos
                            $salarioBase = (float) ($get('salario_base') ?? 0);
                            $insalubridade = (float) ($get('insalubridade') ?? 0);
                            $periculosidade = (float) ($get('periculosidade') ?? 0);
                            $horasExtras = (float) ($get('horas_extras') ?? 0);
                            $adicionalNoturno = (float) ($get('adicional_noturno') ?? 0);
                            $extras = (float) ($get('extras') ?? 0);
                            $valeTransporte = (float) ($get('vale_transporte') ?? 0);
                            $beneficios = (float) ($get('beneficios') ?? 0);

                            $salarioBruto = $salarioBase + $insalubridade + $periculosidade + $horasExtras +
                                           $adicionalNoturno + $extras + $valeTransporte + $beneficios;

                            $set('custo_total_mao_obra', number_format($salarioBruto, 2, '.', ''));
                        }
                    })
                    ->columnSpan(1),

                TextInput::make('encargos_sociais')
                    ->label('Encargos Sociais/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Calculado automaticamente: Salário Bruto × Percentual')
                    ->columnSpan(1),

                TextInput::make('custo_total_mao_obra')
                    ->label('Custo Total de Mão de Obra/Mês')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                    ->prefix('R$')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Calculado automaticamente: Salário Bruto + Encargos Sociais')
                    ->columnSpan(2),

                // Campo oculto para guardar o percentual
                TextInput::make('percentual_encargos')
                    ->hidden()
                    ->dehydrated(),

                Toggle::make('active')
                    ->label('Ativo')
                    ->default(true)
                    ->columnSpan(2),
            ])
            ->columns(2);
    }
}
