<?php

namespace App\Filament\Resources\Marcas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MarcaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('marca')
                    ->label('Marca')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Digite o nome da marca')
                    ->columnSpan(1),

                TextInput::make('mercado')
                    ->label('Mercado')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Digite o mercado da marca')
                    ->columnSpan(1),

                Toggle::make('status')
                    ->label('Status')
                    ->helperText('Ativa/Inativa')
                    ->default(true)
                    ->columnSpan(2),
            ])
            ->columns(2);
    }
}