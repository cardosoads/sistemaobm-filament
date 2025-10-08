<?php

namespace App\Filament\Resources\Colaboradores\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ColaboradorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->required()
                    ->maxLength(100)
                    ->label('Nome Completo'),
                TextInput::make('cpf')
                    ->required()
                    ->maxLength(14)
                    ->label('CPF')
                    ->unique(ignoreRecord: true),
                TextInput::make('rg')
                    ->maxLength(20)
                    ->label('RG'),
                DatePicker::make('data_nascimento')
                    ->label('Data de Nascimento')
                    ->displayFormat('d/m/Y'),
                TextInput::make('telefone')
                    ->tel()
                    ->maxLength(20)
                    ->label('Telefone'),
                TextInput::make('email')
                    ->email()
                    ->maxLength(100)
                    ->label('E-mail'),
                DatePicker::make('data_admissao')
                    ->required()
                    ->label('Data de Admissão')
                    ->displayFormat('d/m/Y'),
                Select::make('cargo_id')
                    ->relationship('cargo', 'cargo')
                    ->required()
                    ->label('Cargo')
                    ->searchable()
                    ->preload(),
                Select::make('base_id')
                    ->relationship('base', 'base')
                    ->required()
                    ->label('Base')
                    ->searchable()
                    ->preload(),
                Toggle::make('status')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }
}
