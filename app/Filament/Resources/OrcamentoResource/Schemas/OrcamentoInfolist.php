<?php

namespace App\Filament\Resources\OrcamentoResource\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class OrcamentoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Informações Gerais do Orçamento
                Section::make('Informações Gerais')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('numero_orcamento')
                                    ->label('Número do Orçamento')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),
                                
                                TextEntry::make('data_orcamento')
                                    ->label('Data do Orçamento')
                                    ->date('d/m/Y'),
                                
                                TextEntry::make('status_formatado')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Em Andamento' => 'warning',
                                        'Enviado' => 'info',
                                        'Aprovado' => 'success',
                                        'Rejeitado' => 'danger',
                                        'Cancelado' => 'gray',
                                        default => 'gray',
                                    }),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('cliente_nome')
                                    ->label('Cliente'),
                                
                                TextEntry::make('nome_rota')
                                    ->label('Nome da Rota'),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('centroCusto.nome')
                                    ->label('Centro de Custo'),
                                
                                TextEntry::make('user.name')
                                    ->label('Usuário Responsável'),
                                
                                TextEntry::make('tipo_orcamento')
                                    ->label('Tipo de Orçamento')
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'prestador' => 'Prestador',
                                        'aumento_km' => 'Aumento KM',
                                        'proprio_nova_rota' => 'Próprio - Nova Rota',
                                        default => $state,
                                    })
                                    ->badge()
                                    ->color('info'),
                            ]),
                        
                        TextEntry::make('frequencia_atendimento_formatada')
                            ->label('Frequência de Atendimento')
                            ->visible(fn ($record) => !empty($record->frequencia_atendimento)),
                        
                        TextEntry::make('observacoes')
                            ->label('Observações')
                            ->columnSpanFull()
                            ->visible(fn ($record) => !empty($record->observacoes)),
                    ]),

                // Valores Totais
                Section::make('Valores Totais')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('valor_total')
                                    ->label('Valor Total')
                                    ->money('BRL')
                                    ->weight(FontWeight::Bold),
                                
                                TextEntry::make('valor_impostos')
                                    ->label('Valor Impostos')
                                    ->money('BRL'),
                                
                                TextEntry::make('valor_final')
                                    ->label('Valor Final')
                                    ->money('BRL')
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),
                                
                                TextEntry::make('grupoImposto.nome')
                                    ->label('Grupo de Imposto'),
                            ]),
                    ]),

                // Seção específica para Prestadores
                Section::make('Detalhes dos Prestadores')
                    ->schema([
                        RepeatableEntry::make('prestadores')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('fornecedor.razao_social')
                                            ->label('Fornecedor')
                                            ->formatStateUsing(fn ($record) => 
                                                $record->fornecedor 
                                                    ? "{$record->fornecedor->codigo_cliente_omie} - {$record->fornecedor->razao_social}" . 
                                                      ($record->fornecedor->nome_fantasia ? " ({$record->fornecedor->nome_fantasia})" : "")
                                                    : $record->fornecedor_nome
                                            ),
                                        
                                        TextEntry::make('valor_referencia')
                                            ->label('Valor de Referência')
                                            ->money('BRL'),
                                        
                                        TextEntry::make('qtd_dias')
                                            ->label('Quantidade de Dias'),
                                    ]),
                                
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('lucro_percentual')
                                            ->label('Lucro (%)')
                                            ->suffix('%'),
                                        
                                        TextEntry::make('impostos_percentual')
                                            ->label('Impostos (%)')
                                            ->suffix('%'),
                                        
                                        TextEntry::make('grupoImposto.nome')
                                            ->label('Grupo de Imposto')
                                            ->formatStateUsing(fn ($record) => 
                                                $record->grupoImposto 
                                                    ? $record->grupoImposto->nome . ' (' . $record->grupoImposto->percentual_total_formatado . ')'
                                                    : '-'
                                            ),
                                    ]),
                                
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('custo_fornecedor')
                                            ->label('Custo Fornecedor')
                                            ->money('BRL'),
                                        
                                        TextEntry::make('valor_lucro')
                                            ->label('Valor Lucro')
                                            ->money('BRL'),
                                        
                                        TextEntry::make('valor_total')
                                            ->label('Valor Total')
                                            ->money('BRL')
                                            ->weight(FontWeight::Bold),
                                    ]),
                            ])
                            ->contained(false),
                    ])
                    ->visible(fn ($record) => $record->tipo_orcamento === 'prestador'),

                // Seção específica para Aumento KM
                Section::make('Detalhes do Aumento KM')
                    ->schema([
                        RepeatableEntry::make('aumentosKm')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('km_por_dia')
                                            ->label('KM por Dia')
                                            ->suffix(' km'),
                                        
                                        TextEntry::make('quantidade_dias_aumento')
                                            ->label('Dias de Aumento'),
                                        
                                        TextEntry::make('combustivel_km_litro')
                                            ->label('Combustível (km/l)')
                                            ->suffix(' km/l'),
                                    ]),
                                
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('valor_combustivel')
                                            ->label('Valor Combustível')
                                            ->money('BRL'),
                                        
                                        TextEntry::make('hora_extra')
                                            ->label('Hora Extra')
                                            ->money('BRL'),
                                        
                                        TextEntry::make('pedagio')
                                            ->label('Pedágio')
                                            ->money('BRL'),
                                        
                                        TextEntry::make('valor_total')
                                            ->label('Valor Total')
                                            ->money('BRL'),
                                    ]),
                                
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('percentual_lucro')
                                            ->label('Lucro (%)')
                                            ->suffix('%'),
                                        
                                        TextEntry::make('valor_lucro')
                                            ->label('Valor Lucro')
                                            ->money('BRL'),
                                        
                                        TextEntry::make('valor_final')
                                            ->label('Valor Final')
                                            ->money('BRL')
                                            ->weight(FontWeight::Bold),
                                    ]),
                            ])
                            ->contained(false),
                    ])
                    ->visible(fn ($record) => $record->tipo_orcamento === 'aumento_km'),

                // Seção específica para Próprio Nova Rota
                Section::make('Detalhes da Nova Rota')
                    ->schema([
                        RepeatableEntry::make('propriosNovaRota')
                            ->schema([
                                // Checkboxes de inclusão
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('incluir_funcionario')
                                            ->label('Incluir Funcionário')
                                            ->formatStateUsing(fn (bool $state): string => $state ? 'Sim' : 'Não')
                                            ->badge()
                                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                                        
                                        TextEntry::make('incluir_frota')
                                            ->label('Incluir Frota')
                                            ->formatStateUsing(fn (bool $state): string => $state ? 'Sim' : 'Não')
                                            ->badge()
                                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                                        
                                        TextEntry::make('incluir_fornecedor')
                                            ->label('Incluir Fornecedor')
                                            ->formatStateUsing(fn (bool $state): string => $state ? 'Sim' : 'Não')
                                            ->badge()
                                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                                    ]),
                                
                                // Dados do Funcionário
                                Group::make([
                                    Grid::make(2)
                                        ->schema([
                                            TextEntry::make('recursoHumano.nome')
                                                ->label('Recurso Humano'),
                                            
                                            TextEntry::make('valor_funcionario')
                                                ->label('Valor Funcionário')
                                                ->money('BRL'),
                                        ]),
                                ])
                                ->visible(fn ($record) => $record->incluir_funcionario),
                                
                                // Dados da Frota
                                Group::make([
                                    Grid::make(2)
                                        ->schema([
                                            TextEntry::make('frota.nome')
                                                ->label('Frota'),
                                            
                                            TextEntry::make('valor_aluguel_frota')
                                                ->label('Valor Aluguel Frota')
                                                ->money('BRL'),
                                        ]),
                                ])
                                ->visible(fn ($record) => $record->incluir_frota),
                                
                                // Dados do Fornecedor
                                Group::make([
                                    Grid::make(3)
                                        ->schema([
                                            TextEntry::make('fornecedor_nome')
                                                ->label('Fornecedor'),
                                            
                                            TextEntry::make('fornecedor_referencia')
                                                ->label('Valor Referência')
                                                ->money('BRL'),
                                            
                                            TextEntry::make('fornecedor_dias')
                                                ->label('Dias'),
                                        ]),
                                ])
                                ->visible(fn ($record) => $record->incluir_fornecedor),
                                
                                // Valores gerais
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('valor_combustivel')
                                            ->label('Valor Combustível')
                                            ->money('BRL'),
                                        
                                        TextEntry::make('valor_pedagio')
                                            ->label('Valor Pedágio')
                                            ->money('BRL'),
                                        
                                        TextEntry::make('valor_total_geral')
                                            ->label('Valor Total Geral')
                                            ->money('BRL'),
                                        
                                        TextEntry::make('quantidade_dias')
                                            ->label('Quantidade de Dias'),
                                    ]),
                                
                                // Valores finais
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('lucro_percentual')
                                            ->label('Lucro (%)')
                                            ->suffix('%'),
                                        
                                        TextEntry::make('valor_lucro')
                                            ->label('Valor Lucro')
                                            ->money('BRL'),
                                        
                                        TextEntry::make('valor_final')
                                            ->label('Valor Final')
                                            ->money('BRL')
                                            ->weight(FontWeight::Bold)
                                            ->color('success'),
                                    ]),
                            ])
                            ->contained(false),
                    ])
                    ->visible(fn ($record) => $record->tipo_orcamento === 'proprio_nova_rota'),
            ]);
    }
}