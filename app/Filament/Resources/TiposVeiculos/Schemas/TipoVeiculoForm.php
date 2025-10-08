<?php

namespace App\Filament\Resources\TiposVeiculos\Schemas;

use App\Models\TipoVeiculo;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TipoVeiculoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codigo')
                    ->label('Código')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Ex: Passeio, P-Cargo')
                    ->columnSpan(1),

                Select::make('tipo_combustivel')
                    ->label('Tipo de Combustível')
                    ->options([
                        'Gasolina' => 'Gasolina',
                        'Etanol' => 'Etanol',
                        'Diesel' => 'Diesel',
                        'Flex' => 'Flex',
                    ])
                    ->required()
                    ->columnSpan(1),

                TextInput::make('consumo_km_litro')
                    ->label('Consumo (KM/L)')
                    ->numeric()
                    ->inputMode('decimal')
                    ->step(0.01)
                    ->required()
                    ->placeholder('Ex: 12.5')
                    ->columnSpan(1),

                Toggle::make('active')
                    ->label('Ativo')
                    ->default(true)
                    ->columnSpan(1),

                Textarea::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->rows(3)
                    ->placeholder('Descrição detalhada do tipo de veículo')
                    ->columnSpan(2),
            ]);
    }
}