<?php

namespace App\Filament\Resources\OrcamentoResource\Tables;

use Filament\Forms;
use Filament\Tables;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction; // exportação Excel
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class OrcamentoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_orcamento')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('nome_rota')
                    ->label('Rota')
                    ->searchable()
                    ->limit(30),
                    
                Tables\Columns\TextColumn::make('cliente_nome')
                    ->label('Cliente')
                    ->searchable()
                    ->limit(25),
                    
                Tables\Columns\BadgeColumn::make('tipo_orcamento')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'prestador',
                        'warning' => 'aumento_km',
                        'success' => 'proprio_nova_rota',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'prestador' => 'Prestador',
                        'aumento_km' => 'Aumento KM',
                        'proprio_nova_rota' => 'Próprio Nova Rota',
                        default => $state,
                    })
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'em_andamento',
                        'success' => 'aprovado',
                        'danger' => 'rejeitado',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'em_andamento' => 'Em andamento',
                        'aprovado' => 'Aprovado',
                        'rejeitado' => 'Rejeitado',
                        default => ucfirst($state),
                    })
                    ->sortable(),
            ])
            ->filters([
                // Defina filtros conforme necessário
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Exportar Excel')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename(fn () => 'orcamentos_' . now()->format('Ymd_His')),
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}