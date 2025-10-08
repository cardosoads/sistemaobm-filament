<?php

namespace App\Filament\Resources\Impostos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ImpostoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ex: ICMS, IPI, ISS'),

                TextInput::make('percentual')
                    ->label('Percentual (%)')
                    ->numeric()
                    ->placeholder('Ex: 18.00')
                    ->required(),

                Toggle::make('ativo')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }
}
