<?php

namespace App\Filament\Resources\ClienteFornecedors\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class ClienteFornecedorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Tipo e Identificação
                Grid::make(2)
                    ->schema([
                        Toggle::make('is_cliente')
                            ->label('É Cliente?')
                            ->helperText('Marque se for cliente, desmarque se for fornecedor')
                            ->default(true)
                            ->required(),
                                
                                Select::make('inativo')
                                    ->label('Status')
                                    ->options([
                                        'N' => 'Ativo',
                                        'S' => 'Inativo',
                                    ])
                                    ->default('N')
                                    ->required(),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('codigo_cliente_omie')
                                    ->label('Código Omie')
                                    ->numeric()
                                    ->disabled()
                                    ->helperText('Preenchido automaticamente pela API'),
                                
                                TextInput::make('codigo_cliente_integracao')
                                    ->label('Código de Integração')
                                    ->helperText('Código interno para integração'),
                            ]),

                // Dados Básicos
                Grid::make(2)
                    ->schema([
                        TextInput::make('razao_social')
                            ->label('Razão Social')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('nome_fantasia')
                            ->label('Nome Fantasia')
                            ->maxLength(255),
                    ]),
                        
                Grid::make(2)
                    ->schema([
                        TextInput::make('cnpj_cpf')
                            ->label('CNPJ/CPF')
                            ->mask('99.999.999/9999-99')
                            ->placeholder('00.000.000/0000-00')
                            ->maxLength(20),
                        
                        Select::make('pessoa_fisica')
                            ->label('Tipo de Pessoa')
                            ->options([
                                'N' => 'Jurídica',
                                'S' => 'Física',
                            ])
                            ->default('N')
                            ->required(),
                    ]),

                Grid::make(2)
                    ->schema([
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),
                        
                        TextInput::make('homepage')
                            ->label('Website')
                            ->url()
                            ->maxLength(255),
                    ]),

                // Contato
                Grid::make(4)
                    ->schema([
                        TextInput::make('telefone1_ddd')
                            ->label('DDD Tel. 1')
                            ->mask('99')
                            ->maxLength(3),
                        
                        TextInput::make('telefone1_numero')
                            ->label('Telefone 1')
                            ->mask('99999-9999')
                            ->maxLength(15),
                        
                        TextInput::make('telefone2_ddd')
                            ->label('DDD Tel. 2')
                            ->mask('99')
                            ->maxLength(3),
                        
                        TextInput::make('telefone2_numero')
                            ->label('Telefone 2')
                            ->mask('99999-9999')
                            ->maxLength(15),
                    ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('fax_ddd')
                                    ->label('DDD Fax')
                                    ->mask('99')
                                    ->maxLength(3),
                                
                                TextInput::make('fax_numero')
                                    ->label('Fax')
                                    ->mask('99999-9999')
                                    ->maxLength(15),
                            ]),

                // Endereço
                Grid::make(3)
                    ->schema([
                        TextInput::make('endereco')
                            ->label('Logradouro')
                            ->columnSpan(2)
                            ->maxLength(255),
                        
                        TextInput::make('endereco_numero')
                            ->label('Número')
                            ->maxLength(10),
                    ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('complemento')
                                    ->label('Complemento')
                                    ->maxLength(255),
                                
                                TextInput::make('bairro')
                                    ->label('Bairro')
                                    ->maxLength(255),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextInput::make('cidade')
                                    ->label('Cidade')
                                    ->maxLength(255),
                                
                                TextInput::make('estado')
                                    ->label('Estado')
                                    ->mask('AA')
                                    ->maxLength(2),
                                
                                TextInput::make('cep')
                                    ->label('CEP')
                                    ->mask('99999-999')
                                    ->maxLength(10),
                            ]),

                // Dados Fiscais
                Grid::make(2)
                    ->schema([
                        TextInput::make('inscricao_estadual')
                            ->label('Inscrição Estadual')
                            ->maxLength(255),
                        
                        TextInput::make('inscricao_municipal')
                            ->label('Inscrição Municipal')
                            ->maxLength(255),
                    ]),
                        
                        Grid::make(3)
                            ->schema([
                                Select::make('optante_simples_nacional')
                                    ->label('Optante Simples Nacional')
                                    ->options([
                                        'N' => 'Não',
                                        'S' => 'Sim',
                                    ])
                                    ->default('N')
                                    ->required(),
                                
                                Select::make('contribuinte')
                                    ->label('Contribuinte')
                                    ->options([
                                        'N' => 'Não',
                                        'S' => 'Sim',
                                    ])
                                    ->default('N')
                                    ->required(),
                                
                                Select::make('exterior')
                                    ->label('Exterior')
                                    ->options([
                                        'N' => 'Nacional',
                                        'S' => 'Exterior',
                                    ])
                                    ->default('N')
                                    ->required(),
                            ]),

                // Observações
                Textarea::make('observacoes')
                    ->label('Observações')
                    ->rows(3)
                    ->columnSpanFull(),
                
                Textarea::make('obs_detalhadas')
                    ->label('Observações Detalhadas')
                    ->rows(3)
                    ->columnSpanFull(),
                
                Textarea::make('recomendacao_atraso')
                    ->label('Recomendação de Atraso')
                    ->rows(2)
                    ->columnSpanFull(),

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
