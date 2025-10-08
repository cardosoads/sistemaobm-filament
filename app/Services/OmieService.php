<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OmieService
{
    protected string $baseUrl;
    protected string $appKey;
    protected string $appSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.omie.url', 'https://app.omie.com.br/api/v1');
        $this->appKey = config('services.omie.app_key');
        $this->appSecret = config('services.omie.app_secret');
    }

    public function buscarCliente(int $clienteId): ?array
    {
        try {
            $cacheKey = "omie_cliente_{$clienteId}";
            
            return Cache::remember($cacheKey, 3600, function () use ($clienteId) {
                $response = Http::timeout(30)->post($this->baseUrl . '/geral/clientes/', [
                    'call' => 'ConsultarCliente',
                    'app_key' => $this->appKey,
                    'app_secret' => $this->appSecret,
                    'param' => [
                        'codigo_cliente_omie' => $clienteId,
                    ],
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['codigo_status']) && $data['codigo_status'] === '0') {
                        return [
                            'id' => $data['codigo_cliente_omie'] ?? $clienteId,
                            'nome' => $data['nome_fantasia'] ?? $data['razao_social'] ?? 'Cliente não encontrado',
                            'cnpj' => $data['cnpj_cpf'] ?? null,
                            'email' => $data['email'] ?? null,
                            'telefone' => $data['telefone1_ddd'] . $data['telefone1_numero'] ?? null,
                            'endereco' => [
                                'logradouro' => $data['endereco'] ?? null,
                                'numero' => $data['endereco_numero'] ?? null,
                                'bairro' => $data['bairro'] ?? null,
                                'cidade' => $data['cidade'] ?? null,
                                'uf' => $data['estado'] ?? null,
                                'cep' => $data['cep'] ?? null,
                            ],
                        ];
                    }
                }
                
                return null;
            });
        } catch (\Exception $e) {
            Log::error('Erro ao buscar cliente Omie', [
                'cliente_id' => $clienteId,
                'erro' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    public function buscarFornecedor(int $fornecedorId): ?array
    {
        try {
            $cacheKey = "omie_fornecedor_{$fornecedorId}";
            
            return Cache::remember($cacheKey, 3600, function () use ($fornecedorId) {
                $response = Http::timeout(30)->post($this->baseUrl . '/geral/fornecedores/', [
                    'call' => 'ConsultarFornecedor',
                    'app_key' => $this->appKey,
                    'app_secret' => $this->appSecret,
                    'param' => [
                        'codigo_fornecedor' => $fornecedorId,
                    ],
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['codigo_status']) && $data['codigo_status'] === '0') {
                        return [
                            'id' => $data['codigo_fornecedor'] ?? $fornecedorId,
                            'nome' => $data['razao_social'] ?? $data['nome_fantasia'] ?? 'Fornecedor não encontrado',
                            'cnpj' => $data['cnpj_cpf'] ?? null,
                            'email' => $data['email'] ?? null,
                            'telefone' => $data['telefone1_ddd'] . $data['telefone1_numero'] ?? null,
                            'endereco' => [
                                'logradouro' => $data['endereco'] ?? null,
                                'numero' => $data['endereco_numero'] ?? null,
                                'bairro' => $data['bairro'] ?? null,
                                'cidade' => $data['cidade'] ?? null,
                                'uf' => $data['estado'] ?? null,
                                'cep' => $data['cep'] ?? null,
                            ],
                        ];
                    }
                }
                
                return null;
            });
        } catch (\Exception $e) {
            Log::error('Erro ao buscar fornecedor Omie', [
                'fornecedor_id' => $fornecedorId,
                'erro' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    public function listarClientes(int $pagina = 1, int $registrosPorPagina = 50): array
    {
        try {
            $response = Http::timeout(30)->post($this->baseUrl . '/geral/clientes/', [
                'call' => 'ListarClientes',
                'app_key' => $this->appKey,
                'app_secret' => $this->appSecret,
                'param' => [
                    'pagina' => $pagina,
                    'registros_por_pagina' => $registrosPorPagina,
                    'apenas_importado_api' => 'N',
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['codigo_status']) && $data['codigo_status'] === '0') {
                    return [
                        'clientes' => array_map(function ($cliente) {
                            return [
                                'id' => $cliente['codigo_cliente_omie'],
                                'nome' => $cliente['nome_fantasia'] ?? $cliente['razao_social'],
                                'cnpj' => $cliente['cnpj_cpf'] ?? null,
                            ];
                        }, $data['clientes_cadastro'] ?? []),
                        'total_paginas' => $data['total_de_paginas'] ?? 1,
                        'total_registros' => $data['total_de_registros'] ?? 0,
                    ];
                }
            }
            
            return ['clientes' => [], 'total_paginas' => 1, 'total_registros' => 0];
        } catch (\Exception $e) {
            Log::error('Erro ao listar clientes Omie', [
                'erro' => $e->getMessage(),
            ]);
            
            return ['clientes' => [], 'total_paginas' => 1, 'total_registros' => 0];
        }
    }

    public function listarFornecedores(int $pagina = 1, int $registrosPorPagina = 50): array
    {
        try {
            $response = Http::timeout(30)->post($this->baseUrl . '/geral/fornecedores/', [
                'call' => 'ListarFornecedores',
                'app_key' => $this->appKey,
                'app_secret' => $this->appSecret,
                'param' => [
                    'pagina' => $pagina,
                    'registros_por_pagina' => $registrosPorPagina,
                    'apenas_importado_api' => 'N',
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['codigo_status']) && $data['codigo_status'] === '0') {
                    return [
                        'fornecedores' => array_map(function ($fornecedor) {
                            return [
                                'id' => $fornecedor['codigo_fornecedor'],
                                'nome' => $fornecedor['razao_social'] ?? $fornecedor['nome_fantasia'],
                                'cnpj' => $fornecedor['cnpj_cpf'] ?? null,
                            ];
                        }, $data['cadastros'] ?? []),
                        'total_paginas' => $data['total_de_paginas'] ?? 1,
                        'total_registros' => $data['total_de_registros'] ?? 0,
                    ];
                }
            }
            
            return ['fornecedores' => [], 'total_paginas' => 1, 'total_registros' => 0];
        } catch (\Exception $e) {
            Log::error('Erro ao listar fornecedores Omie', [
                'erro' => $e->getMessage(),
            ]);
            
            return ['fornecedores' => [], 'total_paginas' => 1, 'total_registros' => 0];
        }
    }

    public function testarConexao(): bool
    {
        try {
            $response = Http::timeout(10)->post($this->baseUrl . '/geral/clientes/', [
                'call' => 'ListarClientes',
                'app_key' => $this->appKey,
                'app_secret' => $this->appSecret,
                'param' => [
                    'pagina' => 1,
                    'registros_por_pagina' => 1,
                ],
            ]);

            return $response->successful() && $response->json('codigo_status') === '0';
        } catch (\Exception $e) {
            Log::error('Erro ao testar conexão com Omie', [
                'erro' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    // Aliases in English for compatibility with existing calls
    public function testConnection(): bool
    {
        return $this->testarConexao();
    }

    public function listClients(int $page = 1, int $perPage = 50, array $filters = []): array
    {
        // Filters parameter is currently unused; kept for signature compatibility.
        return $this->listarClientes($page, $perPage);
    }
}