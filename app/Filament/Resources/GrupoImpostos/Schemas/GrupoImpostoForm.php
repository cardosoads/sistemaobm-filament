<?php

namespace App\Filament\Resources\GrupoImpostos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use App\Models\Imposto;
use Filament\Schemas\Schema;

class GrupoImpostoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->label('Nome do Grupo')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                Textarea::make('descricao')
                    ->label('Descrição')
                    ->rows(3)
                    ->columnSpan(2),

                CheckboxList::make('impostos')
                    ->label('Impostos')
                    ->relationship('impostos')
                    ->options(
                        Imposto::where('ativo', true)
                            ->pluck('nome', 'id')
                            ->toArray()
                    )
                    ->descriptions(
                        Imposto::where('ativo', true)
                            ->get()
                            ->pluck('percentual_formatado', 'id')
                            ->toArray()
                    )
                    ->columns(2)
                    ->live()
                    ->columnSpan(2),

                Placeholder::make('percentual_total')
                    ->label('Percentual Total')
                    ->content(function (Get $get): string {
                        $impostosIds = $get('impostos') ?? [];
                        
                        if (empty($impostosIds)) {
                            return '0,00%';
                        }
                        
                        $total = Imposto::whereIn('id', $impostosIds)
                            ->where('ativo', true)
                            ->sum('percentual');
                        
                        return number_format($total, 2, ',', '.') . '%';
                    })
                    ->columnSpan(1),

                Toggle::make('ativo')
                    ->label('Ativo')
                    ->default(true)
                    ->columnSpan(1),
            ]);
    }
}
