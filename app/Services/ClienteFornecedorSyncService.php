<?php

namespace App\Services;

use App\Models\ClienteFornecedor;
use App\Services\OmieService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClienteFornecedorSyncService
{
    protected $omieService;

    public function __construct(OmieService $omieService)
    {
        $this->omieService = $omieService;
    }

    /**
     * Sincroniza clientes da API Omie
     */
    public function syncClientes(int $pagina = 1, int $registrosPorPagina = 50): array
    {
        try {
            Log::info('Iniciando sincronização de clientes', ['pagina' => $pagina]);

            Log::info('Chamando omieService->listClients', ['pagina' => $pagina, 'registrosPorPagina' => $registrosPorPagina]);
            $response = $this->omieService->listClients($pagina, $registrosPorPagina);
            Log::info('Resposta recebida da API', [
                'response_keys' => array_keys($response ?? []), 
                'success' => $response['success'] ?? false,
                'pagination' => $response['pagination'] ?? null
            ]);
            
            if (!$response || !$response['success']) {
                throw new \Exception('Resposta inválida da API Omie para clientes: ' . ($response['message'] ?? 'Erro desconhecido'));
            }
            
            $clientesData = $response['data'] ?? [];
            Log::info('Dados dos clientes extraídos', ['total_clientes' => count($clientesData)]);

            $clientesProcessados = 0;
            $clientesCriados = 0;
            $clientesAtualizados = 0;
            $erros = [];

            foreach ($clientesData as $clienteData) {
                try {
                    $resultado = $this->processarCliente($clienteData, false); // false = é fornecedor
                    
                    if ($resultado['criado']) {
                        $clientesCriados++;
                    } else {
                        $clientesAtualizados++;
                    }
                    
                    $clientesProcessados++;
                } catch (\Exception $e) {
                    $erros[] = [
                        'codigo_omie' => $clienteData['codigo_cliente_omie'] ?? 'N/A',
                        'erro' => $e->getMessage()
                    ];
                    Log::error('Erro ao processar cliente', [
                        'codigo_omie' => $clienteData['codigo_cliente_omie'] ?? 'N/A',
                        'erro' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Sincronização de clientes concluída', [
                'processados' => $clientesProcessados,
                'criados' => $clientesCriados,
                'atualizados' => $clientesAtualizados,
                'erros' => count($erros)
            ]);

            return [
                'sucesso' => true,
                'processados' => $clientesProcessados,
                'criados' => $clientesCriados,
                'atualizados' => $clientesAtualizados,
                'erros' => $erros,
                'total_paginas' => $response['pagination']['total_pages'] ?? 1,
                'pagina_atual' => $response['pagination']['current_page'] ?? 1,
                'total_registros' => $response['pagination']['total'] ?? 0
            ];

        } catch (\Exception $e) {
            Log::error('Erro na sincronização de clientes', ['erro' => $e->getMessage()]);
            
            return [
                'sucesso' => false,
                'erro' => $e->getMessage(),
                'processados' => 0,
                'criados' => 0,
                'atualizados' => 0,
                'erros' => []
            ];
        }
    }

    /**
     * Sincroniza fornecedores da API Omie (usando o mesmo método listClients)
     */
    public function syncFornecedores(int $pagina = 1, int $registrosPorPagina = 50): array
    {
        try {
            Log::info('Iniciando sincronização de fornecedores', ['pagina' => $pagina]);

            Log::info('Chamando omieService->listSuppliers', ['pagina' => $pagina, 'registrosPorPagina' => $registrosPorPagina]);
            $response = $this->omieService->listSuppliers($pagina, $registrosPorPagina);
            Log::info('Resposta recebida da API para fornecedores', ['response_keys' => array_keys($response ?? []), 'success' => $response['success'] ?? false]);
            
            if (!$response || !$response['success']) {
                throw new \Exception('Resposta inválida da API Omie para fornecedores: ' . ($response['message'] ?? 'Erro desconhecido'));
            }
            
            $fornecedoresData = $response['data'] ?? [];
            Log::info('Dados dos fornecedores extraídos', ['total_fornecedores' => count($fornecedoresData)]);

            $fornecedoresProcessados = 0;
            $fornecedoresAtualizados = 0;
            $fornecedoresCriados = 0;
            $erros = [];

            foreach ($fornecedoresData as $fornecedorData) {
                try {
                    $resultado = $this->processarCliente($fornecedorData, false); // false = é fornecedor
                    
                    if ($resultado['criado']) {
                        $fornecedoresCriados++;
                    } else {
                        $fornecedoresAtualizados++;
                    }
                    
                    $fornecedoresProcessados++;
                } catch (\Exception $e) {
                    $erros[] = [
                        'codigo_omie' => $fornecedorData['codigo_cliente_omie'] ?? 'N/A',
                        'erro' => $e->getMessage()
                    ];
                    Log::error('Erro ao processar fornecedor', [
                        'codigo_omie' => $fornecedorData['codigo_cliente_omie'] ?? 'N/A',
                        'erro' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Sincronização de fornecedores concluída', [
                'processados' => $fornecedoresProcessados,
                'criados' => $fornecedoresCriados,
                'atualizados' => $fornecedoresAtualizados,
                'erros' => count($erros)
            ]);

            return [
                'sucesso' => true,
                'processados' => $fornecedoresProcessados,
                'criados' => $fornecedoresCriados,
                'atualizados' => $fornecedoresAtualizados,
                'erros' => $erros,
                'total_paginas' => $response['pagination']['total_pages'] ?? 1,
                'pagina_atual' => $response['pagination']['current_page'] ?? 1,
                'total_registros' => $response['pagination']['total'] ?? 0
            ];

        } catch (\Exception $e) {
            Log::error('Erro na sincronização de fornecedores', ['erro' => $e->getMessage()]);
            
            return [
                'sucesso' => false,
                'erro' => $e->getMessage(),
                'processados' => 0,
                'criados' => 0,
                'atualizados' => 0,
                'erros' => []
            ];
        }
    }

    /**
     * Sincroniza todos os clientes e fornecedores
     */
    public function syncTodos(): array
    {
        $resultadoGeral = [
            'clientes' => ['processados' => 0, 'criados' => 0, 'atualizados' => 0, 'erros' => []],
            'fornecedores' => ['processados' => 0, 'criados' => 0, 'atualizados' => 0, 'erros' => []],
            'sucesso' => true,
            'mensagem' => ''
        ];

        try {
            // Sincronizar clientes
            $paginaClientes = 1;
            do {
                $resultadoClientes = $this->syncClientes($paginaClientes);
                
                if (!$resultadoClientes['sucesso']) {
                    $resultadoGeral['sucesso'] = false;
                    $resultadoGeral['mensagem'] .= "Erro na sincronização de clientes: " . $resultadoClientes['erro'] . "; ";
                    break;
                }

                $resultadoGeral['clientes']['processados'] += $resultadoClientes['processados'];
                $resultadoGeral['clientes']['criados'] += $resultadoClientes['criados'];
                $resultadoGeral['clientes']['atualizados'] += $resultadoClientes['atualizados'];
                $resultadoGeral['clientes']['erros'] = array_merge($resultadoGeral['clientes']['erros'], $resultadoClientes['erros']);



                $paginaClientes++;
            } while ($paginaClientes <= ($resultadoClientes['total_paginas'] ?? 1));

            // Sincronizar fornecedores (comentado por enquanto, pois usa o mesmo endpoint)
            /*
            $paginaFornecedores = 1;
            do {
                $resultadoFornecedores = $this->syncFornecedores($paginaFornecedores);
                
                if (!$resultadoFornecedores['sucesso']) {
                    $resultadoGeral['sucesso'] = false;
                    $resultadoGeral['mensagem'] .= "Erro na sincronização de fornecedores: " . $resultadoFornecedores['erro'] . "; ";
                    break;
                }

                $resultadoGeral['fornecedores']['processados'] += $resultadoFornecedores['processados'];
                $resultadoGeral['fornecedores']['criados'] += $resultadoFornecedores['criados'];
                $resultadoGeral['fornecedores']['atualizados'] += $resultadoFornecedores['atualizados'];
                $resultadoGeral['fornecedores']['erros'] = array_merge($resultadoGeral['fornecedores']['erros'], $resultadoFornecedores['erros']);

                $paginaFornecedores++;
            } while ($paginaFornecedores <= ($resultadoFornecedores['total_paginas'] ?? 1));
            */

            if ($resultadoGeral['sucesso']) {
                $resultadoGeral['mensagem'] = 'Sincronização concluída com sucesso';
            }

        } catch (\Exception $e) {
            $resultadoGeral['sucesso'] = false;
            $resultadoGeral['mensagem'] = 'Erro geral na sincronização: ' . $e->getMessage();
            Log::error('Erro geral na sincronização', ['erro' => $e->getMessage()]);
        }

        return $resultadoGeral;
    }

    /**
     * Processa um cliente/fornecedor individual
     */
    protected function processarCliente(array $data, bool $isCliente): array
    {
        $codigoOmie = $data['codigo_cliente_omie'] ?? null;
        
        if (!$codigoOmie) {
            throw new \Exception('Código Omie não encontrado nos dados');
        }

        // Buscar registro existente (independente de ser cliente ou fornecedor)
        // Se o usuário quer que todos venham como fornecedor, vamos buscar qualquer registro com o mesmo código
        $clienteFornecedor = ClienteFornecedor::where('codigo_cliente_omie', $codigoOmie)->first();

        $dadosParaSalvar = $this->mapearDadosApi($data, $isCliente);

        if ($clienteFornecedor) {
            // Atualizar registro existente (mudando o tipo se necessário)
            $clienteFornecedor->update($dadosParaSalvar);
            $clienteFornecedor->marcarComoSincronizado();
            
            return ['criado' => false, 'registro' => $clienteFornecedor];
        } else {
            // Criar novo registro
            $clienteFornecedor = ClienteFornecedor::create($dadosParaSalvar);
            $clienteFornecedor->marcarComoSincronizado();
            
            return ['criado' => true, 'registro' => $clienteFornecedor];
        }
    }

    /**
     * Mapeia dados da API para o formato do banco
     */
    protected function mapearDadosApi(array $data, bool $isCliente): array
    {
        return [
            'is_cliente' => $isCliente,
            'codigo_cliente_omie' => $data['codigo_cliente_omie'] ?? null,
            'codigo_cliente_integracao' => $data['codigo_cliente_integracao'] ?? null,
            'razao_social' => $data['razao_social'] ?? null,
            'nome_fantasia' => $data['nome_fantasia'] ?? null,
            'cnpj_cpf' => $data['cnpj_cpf'] ?? null,
            'email' => $data['email'] ?? null,
            'homepage' => $data['homepage'] ?? null,
            'telefone1_ddd' => $data['telefone1_ddd'] ?? null,
            'telefone1_numero' => $data['telefone1_numero'] ?? null,
            'telefone2_ddd' => $data['telefone2_ddd'] ?? null,
            'telefone2_numero' => $data['telefone2_numero'] ?? null,
            'fax_ddd' => $data['fax_ddd'] ?? null,
            'fax_numero' => $data['fax_numero'] ?? null,
            'endereco' => $data['endereco'] ?? null,
            'endereco_numero' => $data['endereco_numero'] ?? null,
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'cidade' => $data['cidade'] ?? null,
            'estado' => $data['estado'] ?? null,
            'cep' => $data['cep'] ?? null,
            'inativo' => $data['inativo'] ?? 'N',
            'inscricao_estadual' => $data['inscricao_estadual'] ?? null,
            'inscricao_municipal' => $data['inscricao_municipal'] ?? null,
            'pessoa_fisica' => $data['pessoa_fisica'] ?? 'N',
            'optante_simples_nacional' => $data['optante_simples_nacional'] ?? 'N',
            'contribuinte' => $data['contribuinte'] ?? 'N',
            'exterior' => $data['exterior'] ?? 'N',
            'importado_api' => 'S',
            'observacao' => $data['observacao'] ?? null,
            'obs_detalhadas' => $data['obs_detalhadas'] ?? null,
            'dados_originais_api' => json_encode($data),
            'data_inclusao' => isset($data['data_inclusao']) ? Carbon::parse($data['data_inclusao']) : null,
            'data_alteracao' => isset($data['data_alteracao']) ? Carbon::parse($data['data_alteracao']) : null,
        ];
    }
}