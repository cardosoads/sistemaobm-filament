<?php

namespace App\Filament\Resources\Combustivels\Schemas;

use App\Models\Base;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CombustivelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('base_id')
                    ->label('Base')
                    ->relationship('base', 'base')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->placeholder('Selecione uma base')
                    ->columnSpan(1),

                TextInput::make('convenio')
                    ->label('Convênio')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ex: Posto Shell, BR Distribuidora')
                    ->columnSpan(1),

                TextInput::make('preco_litro')
                    ->label('Preço por Litro (R$)')
                    ->numeric()
                    ->step(0.001)
                    ->required()
                    ->placeholder('Ex: 5.299')
                    ->prefix('R$')
                    ->columnSpan(1),

                Toggle::make('active')
                    ->label('Ativo')
                    ->default(true)
                    ->columnSpan(1),
            ])
            ->columns(2);
    }
}
