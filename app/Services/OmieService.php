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
        $this->baseUrl = rtrim(config('services.omie.api_url', 'https://app.omie.com.br/api/v1'), '/');
        $this->appKey = (string) config('services.omie.app_key', '');
        $this->appSecret = (string) config('services.omie.app_secret', '');

        if (empty($this->appKey) || empty($this->appSecret)) {
            throw new \Exception('OMIE_APP_KEY and OMIE_APP_SECRET must be set in the environment.');
        }
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
                
                // Se há dados de clientes ou se não há erro, considera sucesso
                if (isset($data['clientes_cadastro']) || !isset($data['faultstring'])) {
                    return [
                        'success' => true,
                        'data' => $data['clientes_cadastro'] ?? [],
                        'pagination' => [
                            'current_page' => $pagina,
                            'total_pages' => $data['total_de_paginas'] ?? 1,
                            'total' => $data['total_de_registros'] ?? 0,
                        ]
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => 'Erro da API: ' . ($data['faultstring'] ?? 'Erro desconhecido'),
                    'data' => [],
                    'pagination' => ['current_page' => 1, 'total_pages' => 1, 'total' => 0]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Resposta inválida da API Omie',
                'data' => [],
                'pagination' => ['current_page' => 1, 'total_pages' => 1, 'total' => 0]
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao listar clientes Omie', [
                'erro' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
                'pagination' => ['current_page' => 1, 'total_pages' => 1, 'total' => 0]
            ];
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
                
                // Se há dados de fornecedores ou se não há erro, considera sucesso
                if (isset($data['cadastros']) || !isset($data['faultstring'])) {
                    return [
                        'success' => true,
                        'data' => $data['cadastros'] ?? [],
                        'pagination' => [
                            'current_page' => $pagina,
                            'total_pages' => $data['total_de_paginas'] ?? 1,
                            'total' => $data['total_de_registros'] ?? 0,
                        ]
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => 'Erro da API: ' . ($data['faultstring'] ?? 'Erro desconhecido'),
                    'data' => [],
                    'pagination' => ['current_page' => 1, 'total_pages' => 1, 'total' => 0]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Resposta inválida da API Omie',
                'data' => [],
                'pagination' => ['current_page' => 1, 'total_pages' => 1, 'total' => 0]
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao listar fornecedores Omie', [
                'erro' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
                'pagination' => ['current_page' => 1, 'total_pages' => 1, 'total' => 0]
            ];
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
    public function testConnection(): array
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

            if ($response->successful()) {
                $data = $response->json();
                
                // Se há dados de clientes ou se não há erro, considera sucesso
                if (isset($data['clientes_cadastro']) || !isset($data['faultstring'])) {
                    return [
                        'success' => true,
                        'message' => 'Conexão estabelecida com sucesso'
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => 'Erro da API: ' . ($data['faultstring'] ?? 'Erro desconhecido')
                ];
            }

            return [
                'success' => false,
                'message' => 'Falha na requisição HTTP: ' . $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao testar conexão com Omie', [
                'erro' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function listClients(int $page = 1, int $perPage = 50, array $filters = []): array
    {
        // Filters parameter is currently unused; kept for signature compatibility.
        return $this->listarClientes($page, $perPage);
    }

    public function listSuppliers(int $page = 1, int $perPage = 50, array $filters = []): array
    {
        return $this->listarFornecedores($page, $perPage);
    }

    public function listDepartments(int $page = 1, int $perPage = 50, array $filters = []): array
    {
        try {
            $response = Http::timeout(30)->post($this->baseUrl . '/geral/departamentos/', [
                'call' => 'ListarDepartamentos',
                'app_key' => $this->appKey,
                'app_secret' => $this->appSecret,
                'param' => [
                    'pagina' => $page,
                    'registros_por_pagina' => $perPage,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Se há dados de departamentos ou se não há erro, considera sucesso
                if (isset($data['departamentos']) || !isset($data['faultstring'])) {
                    return [
                        'success' => true,
                        'data' => array_map(function ($departamento) {
                            return [
                                'codigo_departamento_omie' => $departamento['codigo'] ?? null,
                                'codigo_departamento_integracao' => $departamento['codigo_departamento_integracao'] ?? null,
                                'nome_departamento' => $departamento['descricao'] ?? null,
                                'descricao_departamento' => $departamento['descricao'] ?? null,
                                'estrutura' => $departamento['estrutura'] ?? null,
                                'inativo' => $departamento['inativo'] ?? 'N',
                            ];
                        }, $data['departamentos'] ?? []),
                        'pagination' => [
                            'current_page' => $page,
                            'total_pages' => $data['total_de_paginas'] ?? 1,
                            'total' => $data['total_de_registros'] ?? 0,
                        ]
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => 'Erro da API: ' . ($data['faultstring'] ?? 'Erro desconhecido'),
                    'data' => [],
                    'pagination' => ['current_page' => 1, 'total_pages' => 1, 'total' => 0]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Resposta inválida da API Omie',
                'data' => [],
                'pagination' => ['current_page' => 1, 'total_pages' => 1, 'total' => 0]
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao listar departamentos Omie', [
                'erro' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
                'pagination' => ['current_page' => 1, 'total_pages' => 1, 'total' => 0]
            ];
        }
    }
}
