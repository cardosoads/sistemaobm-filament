<?php

namespace App\Filament\Resources\OrcamentoResource\RelationManagers\Schemas;

use Filament\Forms\Components;
use Filament\Schemas\Schema;

class AumentosKmForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make('Informações de KM')
                    ->schema([
                        Components\TextInput::make('km_rodado')
                            ->label('KM Rodado')
                            ->numeric()
                            ->required()
                            ->default(0),
                            
                        Components\TextInput::make('km_extra')
                            ->label('KM Extra')
                            ->numeric()
                            ->required()
                            ->default(0),
                            
                        Components\TextInput::make('valor_km_extra')
                            ->label('Valor KM Extra')
                            ->numeric()
                            ->prefix('R$')
                            ->required()
                            ->default(0),
                    ])
                    ->columns(3),
                    
                Components\Section::make('Combustível')
                    ->schema([
                        Components\TextInput::make('litros_combustivel')
                            ->label('Litros Combustível')
                            ->numeric()
                            ->required()
                            ->default(0),
                            
                        Components\TextInput::make('valor_combustivel')
                            ->label('Valor Combustível')
                            ->numeric()
                            ->prefix('R$')
                            ->required()
                            ->default(0),
                    ])
                    ->columns(2),
                    
                Components\Section::make('Horas Extras e Pedágio')
                    ->schema([
                        Components\TextInput::make('horas_extras')
                            ->label('Horas Extras')
                            ->numeric()
                            ->required()
                            ->default(0),
                            
                        Components\TextInput::make('valor_hora_extra')
                            ->label('Valor Hora Extra')
                            ->numeric()
                            ->prefix('R$')
                            ->required()
                            ->default(0),
                            
                        Components\TextInput::make('valor_pedagio')
                            ->label('Valor Pedágio')
                            ->numeric()
                            ->prefix('R$')
                            ->required()
                            ->default(0),
                    ])
                    ->columns(3),
                    
                Components\Section::make('Lucro e Impostos')
                    ->schema([
                        Components\TextInput::make('lucro_percentual')
                            ->label('Percentual de Lucro (%)')
                            ->numeric()
                            ->suffix('%')
                            ->required()
                            ->default(20),
                            
                        Components\Select::make('grupo_imposto_id')
                            ->label('Grupo de Impostos')
                            ->relationship('grupoImposto', 'nome')
                            ->getOptionLabelFromRecordUsing(fn (\App\Models\GrupoImposto $record) => 
                                $record->nome . ' (' . $record->percentual_total_formatado . ')'
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2),
                    
                Components\Section::make('Valores Calculados')
                    ->schema([
                        Components\TextInput::make('valor_total')
                            ->label('Valor Total')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated(),
                            
                        Components\TextInput::make('valor_lucro')
                            ->label('Valor do Lucro')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated(),
                            
                        Components\TextInput::make('valor_impostos')
                            ->label('Valor dos Impostos')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated(),
                            
                        Components\TextInput::make('valor_final')
                            ->label('Valor Final')
                            ->numeric()
                            ->prefix('R$')
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(2),
            ]);
    }
}