<?php

namespace App\Filament\Resources\CentroCustos\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CentroCustoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Identificação
                Grid::make(2)
                    ->schema([
                        TextInput::make('codigo_departamento_omie')
                            ->label('Código Omie')
                            ->numeric()
                            ->disabled()
                            ->helperText('Preenchido automaticamente pela API'),
                        
                        TextInput::make('codigo_departamento_integracao')
                            ->label('Código de Integração')
                            ->helperText('Código interno para integração'),
                    ]),

                // Dados Básicos
                Grid::make(2)
                    ->schema([
                        TextInput::make('nome')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        
                        Select::make('inativo')
                            ->label('Status')
                            ->options([
                                'N' => 'Ativo',
                                'S' => 'Inativo',
                            ])
                            ->default('N')
                            ->required(),
                    ]),

                Textarea::make('descricao')
                    ->label('Descrição')
                    ->rows(3)
                    ->columnSpanFull(),

                // Associação com Cliente
                Select::make('cliente_id')
                    ->label('Cliente Associado')
                    ->relationship('cliente', 'razao_social', fn ($query) => $query->where('is_cliente', true)->where('inativo', 'N'))
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Campo específico do sistema OBM, preenchido após sincronização'),

                // Base e Localização
                Grid::make(2)
                    ->schema([
                        Select::make('base_id')
                            ->label('Base')
                            ->relationship(
                                'base', 
                                'base',
                                fn ($query) => $query->where('status', true)
                                    ->orderBy('regional')
                                    ->orderBy('base')
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                "{$record->base} ({$record->sigla}) - {$record->regional}/{$record->uf}"
                            )
                            ->searchable(['base', 'sigla', 'regional', 'uf'])
                            ->preload()
                            ->nullable()
                            ->helperText('Pesquise por cidade, sigla, regional ou UF')
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Quando a base é selecionada, podemos preencher automaticamente outros campos se necessário
                                if ($state) {
                                    $base = \App\Models\Base::find($state);
                                    if ($base && $base->supervisor) {
                                        $set('supervisor', $base->supervisor);
                                    }
                                }
                            }),
                        
                        TextInput::make('supervisor')
                            ->label('Supervisor')
                            ->maxLength(255)
                            ->nullable()
                            ->helperText('Supervisor da base (editável)'),
                    ]),

                // Controle de Sincronização
                Grid::make(3)
                    ->schema([
                        Select::make('status_sincronizacao')
                            ->label('Status da Sincronização')
                            ->options([
                                'pendente' => 'Pendente',
                                'sincronizado' => 'Sincronizado',
                                'erro' => 'Erro',
                            ])
                            ->default('pendente')
                            ->required(),
                        
                        DateTimePicker::make('ultima_sincronizacao')
                            ->label('Última Sincronização')
                            ->disabled(),
                        
                        Select::make('importado_api')
                            ->label('Importado da API')
                            ->options([
                                'N' => 'Manual',
                                'S' => 'Importado',
                            ])
                            ->default('N')
                            ->required(),
                    ]),
                
                Grid::make(2)
                    ->schema([
                        DateTimePicker::make('data_inclusao')
                            ->label('Data de Inclusão')
                            ->disabled(),
                        
                        DateTimePicker::make('data_alteracao')
                            ->label('Data de Alteração')
                            ->disabled(),
                    ]),
            ]);
    }
}
