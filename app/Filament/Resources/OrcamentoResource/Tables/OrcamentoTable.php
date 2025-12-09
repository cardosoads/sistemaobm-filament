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
                    
                Tables\Columns\TextColumn::make('prestador_nome')
                    ->label('Prestador')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        // Para tipo prestador, buscar na tabela orcamento_prestador
                        if ($record->tipo_orcamento === 'prestador') {
                            // Usar o relacionamento já carregado via eager loading
                            $prestador = $record->prestadores->first();
                            if ($prestador) {
                                // Priorizar o nome salvo diretamente no campo fornecedor_nome
                                if (!empty($prestador->fornecedor_nome)) {
                                    return $prestador->fornecedor_nome;
                                }
                                // Fallback: buscar pelo relacionamento fornecedor (já carregado)
                                if ($prestador->fornecedor) {
                                    return $prestador->fornecedor->nome_fantasia 
                                        ?? $prestador->fornecedor->razao_social 
                                        ?? null;
                                }
                            }
                        }
                        
                        // Para nova rota, verificar se incluir_fornecedor está marcado
                        if ($record->tipo_orcamento === 'proprio_nova_rota') {
                            // Usar o relacionamento já carregado via eager loading
                            $proprioNovaRota = $record->propriosNovaRota->first();
                            if ($proprioNovaRota && $proprioNovaRota->incluir_fornecedor) {
                                // Priorizar o nome salvo diretamente no campo fornecedor_nome
                                if (!empty($proprioNovaRota->fornecedor_nome)) {
                                    return $proprioNovaRota->fornecedor_nome;
                                }
                                // Fallback: buscar pelo relacionamento fornecedor (já carregado)
                                if ($proprioNovaRota->fornecedor) {
                                    return $proprioNovaRota->fornecedor->nome_fantasia 
                                        ?? $proprioNovaRota->fornecedor->razao_social 
                                        ?? null;
                                }
                            }
                        }
                        
                        return null;
                    })
                    ->placeholder('N/A')
                    ->toggleable()
                    ->visible(fn ($record) => in_array($record->tipo_orcamento ?? '', ['prestador', 'proprio_nova_rota'])),
                    
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