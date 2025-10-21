<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Digite o nome do usuário')
                    ->columnSpan(1),

                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('Digite o e-mail do usuário')
                    ->columnSpan(1),

                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255)
                    ->placeholder('Digite a senha do usuário')
                    ->columnSpan(1),

                TextInput::make('password_confirmation')
                    ->label('Confirmação de Senha')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->same('password')
                    ->maxLength(255)
                    ->placeholder('Confirme a senha do usuário')
                    ->columnSpan(1),

                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Papéis')
                    ->columnSpan(2),
            ])
            ->columns(2);
    }
}