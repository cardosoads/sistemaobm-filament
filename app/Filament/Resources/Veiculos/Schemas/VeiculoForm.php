<?php

namespace App\Filament\Resources\Veiculos\Schemas;

use App\Models\TipoVeiculo;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class VeiculoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        TextInput::make('placa')
                            ->label('Placa')
                            ->required()
                            ->maxLength(8)
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(1),

                Grid::make()
                    ->schema([
                        TextInput::make('renavam')
                            ->label('Renavam')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(1),

                Grid::make()
                    ->schema([
                        TextInput::make('chassi')
                            ->label('Chassi')
                            ->required()
                            ->maxLength(30)
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(1),

                Grid::make()
                    ->schema([
                        TextInput::make('ano_modelo')
                            ->label('Ano/Modelo')
                            ->required()
                            ->maxLength(9),
                    ])
                    ->columns(1),

                Grid::make()
                    ->schema([
                        TextInput::make('cor')
                            ->label('Cor')
                            ->required()
                            ->maxLength(50),
                    ])
                    ->columns(1),

                Grid::make()
                    ->schema([
                        TextInput::make('marca_modelo')
                            ->label('Marca/Modelo')
                            ->required()
                            ->maxLength(100),
                    ])
                    ->columns(1),

                Grid::make()
                    ->schema([
                        Select::make('tipo_combustivel')
                            ->label('Tipo de Combustível')
                            ->required()
                            ->options([
                                'gasolina' => 'Gasolina',
                                'alcool' => 'Álcool',
                                'diesel' => 'Diesel',
                                'gnv' => 'GNV',
                                'flex' => 'Flex',
                                'eletrico' => 'Elétrico',
                                'hibrido' => 'Híbrido',
                            ]),
                    ])
                    ->columns(1),

                Grid::make()
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'ativo' => 'Ativo',
                                'inativo' => 'Inativo',
                                'manutencao' => 'Manutenção',
                                'vendido' => 'Vendido',
                            ])
                            ->default('ativo'),
                    ])
                    ->columns(1),

                Grid::make()
                    ->schema([
                        Select::make('tipo_veiculo_id')
                            ->label('Tipo de Veículo')
                            ->required()
                            ->relationship('tipoVeiculo', 'codigo')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(1),
            ]);
    }
}
