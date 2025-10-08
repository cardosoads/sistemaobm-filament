<?php

namespace App\Filament\Resources\Bases\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class BaseInfolist
{
    public static function configure(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informações da Base')
                    ->description('Dados principais da base operacional')
                    ->schema([
                        TextEntry::make('uf')
                            ->label('UF (Estado)')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('base')
                            ->label('BASE (Cidade)')
                            ->weight('medium'),

                        TextEntry::make('regional')
                            ->label('Regional')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Norte' => 'success',
                                'Nordeste' => 'warning',
                                'Centro-Oeste' => 'info',
                                'Sudeste' => 'primary',
                                'Sul' => 'secondary',
                                default => 'gray',
                            }),

                        TextEntry::make('sigla')
                            ->label('Sigla')
                            ->copyable()
                            ->copyMessage('Sigla copiada!')
                            ->weight('bold'),

                        IconEntry::make('status')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                    ])
                    ->columns(2),

                Section::make('Informações do Sistema')
                    ->description('Dados de controle e auditoria')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime('d/m/Y H:i:s'),

                        TextEntry::make('updated_at')
                            ->label('Atualizado em')
                            ->dateTime('d/m/Y H:i:s'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
