<?php

namespace App\Filament\Resources\Marcas\Schemas;

use Filament\Infolists;
use Filament\Infolists\Infolist;

class MarcaInfolist
{
    public static function configure(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informações da Marca')
                    ->schema([
                        Infolists\Components\TextEntry::make('marca')
                            ->label('Marca'),
                        Infolists\Components\TextEntry::make('mercado')
                            ->label('Mercado'),
                        Infolists\Components\IconEntry::make('status')
                            ->label('Status')
                            ->boolean(),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Informações do Sistema')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Atualizado em')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}