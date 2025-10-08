<?php

namespace App\Filament\Resources\Bases\Schemas;

use App\Models\Base;
use App\Services\CorreiosService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('uf')
                    ->label('UF (Estado)')
                    ->options(CorreiosService::getUfs())
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                        // Limpa a cidade quando UF muda
                        $set('base', null);
                        
                        // Define a regional automaticamente
                        if ($state) {
                            $regional = Base::getRegionalByUf($state);
                            $set('regional', $regional);
                        }
                    })
                    ->columnSpan(1),

                Select::make('base')
                    ->label('BASE (Cidade)')
                    ->options(function (Get $get): array {
                        $uf = $get('uf');
                        if (!$uf) {
                            return [];
                        }
                        return CorreiosService::getCidadesByUf($uf);
                    })
                    ->required()
                    ->searchable()
                    ->disabled(fn (Get $get): bool => !$get('uf'))
                    ->helperText('Selecione primeiro a UF para carregar as cidades')
                    ->columnSpan(1),

                TextInput::make('regional')
                    ->label('Regional')
                    ->required()
                    ->readonly()
                    ->helperText('Preenchido automaticamente baseado na UF')
                    ->columnSpan(1),

                TextInput::make('sigla')
                    ->label('Sigla')
                    ->required()
                    ->maxLength(10)
                    ->placeholder('Ex: OBM-SP-01')
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
