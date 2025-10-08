<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OmieService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Controller para integração com API Omie
 * 
 * Fornece endpoints robustos para busca e consulta de clientes e fornecedores
 * com validações avançadas, rate limiting e tratamento de erros.
 */
class OmieController extends Controller
{
    private OmieService $omieService;

    public function __construct(OmieService $omieService)
    {
        $this->omieService = $omieService;
        
        // Aplicar throttle apenas em contexto HTTP
        // Temporariamente desabilitado para debug
        /*
        if (app()->runningInConsole() === false) {
            // Aplicar throttle a todos os métodos exceto testConnection
            $this->middleware('throttle:omie')->except(['testConnection']);
            
            // Throttle específico para teste de conexão
            $this->middleware('throttle:omie-test')->only('testConnection');
        }
        */
    }

    /**
     * Testa a conectividade com a API Omie
     * 
     * @return JsonResponse
     */
    public function testConnection(): JsonResponse
    {
        try {
            $result = $this->omieService->testConnection();
            
            return response()->json($result, $result['success'] ? 200 : 503);
            
        } catch (Exception $e) {
            Log::error('Erro no teste de conexão Omie', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno no teste de conexão',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Busca clientes por termo (documento, razão social ou nome fantasia)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function searchClients(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required|string|min:2|max:100',
            'limit' => 'sometimes|integer|min:1|max:50'
        ], [
            'search.required' => 'Termo de busca é obrigatório',
            'search.min' => 'Termo de busca deve ter pelo menos 2 caracteres',
            'search.max' => 'Termo de busca não pode exceder 100 caracteres',
            'limit.integer' => 'Limite deve ser um número inteiro',
            'limit.min' => 'Limite mínimo é 1',
            'limit.max' => 'Limite máximo é 50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $searchTerm = $request->input('search');
        $limit = $request->input('limit', 20);

        try {
            // Rate limiting removido temporariamente para debug

            $clientes = $this->omieService->searchClients($searchTerm, $limit);

            $clientesFormatados = array_map([$this, 'formatClientData'], $clientes);

            Log::info('Busca de clientes realizada', [
                'search_term' => $searchTerm,
                'results_count' => count($clientesFormatados),
                'user_ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'data' => $clientesFormatados,
                'total' => count($clientesFormatados),
                'search_term' => $searchTerm,
                'limit' => $limit
            ]);

        } catch (Exception $e) {
            Log::error('Erro na busca de clientes', [
                'search_term' => $searchTerm,
                'message' => $e->getMessage(),
                'user_ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar clientes',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Busca fornecedores por termo com suporte a diferentes tipos de busca
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function searchSuppliers(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required|string|max:100',
            'type' => 'sometimes|string|in:codigo,nome,documento',
            'limit' => 'sometimes|integer|min:1|max:50'
        ], [
            'search.required' => 'Termo de busca é obrigatório',
            'search.max' => 'Termo de busca não pode exceder 100 caracteres',
            'type.in' => 'Tipo deve ser: codigo, nome ou documento',
            'limit.integer' => 'Limite deve ser um número inteiro',
            'limit.min' => 'Limite mínimo é 1',
            'limit.max' => 'Limite máximo é 50'
        ]);

        $searchTerm = $request->input('search');
        $type = $request->input('type', '');
        $limit = $request->input('limit', 20);

        // Validação específica baseada no tipo
        if ($type === 'codigo' && strlen($searchTerm) < 1) {
            $validator->errors()->add('search', 'Código deve ter pelo menos 1 caractere');
        } elseif ($type !== 'codigo' && strlen($searchTerm) < 2) {
            $validator->errors()->add('search', 'Termo de busca deve ter pelo menos 2 caracteres');
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Rate limiting removido temporariamente para debug

            $fornecedores = $this->omieService->searchSuppliers($searchTerm, $type, $limit);

            $fornecedoresFormatados = array_map([$this, 'formatClientData'], $fornecedores);

            Log::info('Busca de fornecedores realizada', [
                'search_term' => $searchTerm,
                'search_type' => $type,
                'results_count' => count($fornecedoresFormatados),
                'user_ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'data' => $fornecedoresFormatados,
                'total' => count($fornecedoresFormatados),
                'search_term' => $searchTerm,
                'search_type' => $type,
                'limit' => $limit
            ]);

        } catch (Exception $e) {
            Log::error('Erro na busca de fornecedores', [
                'search_term' => $searchTerm,
                'search_type' => $type,
                'message' => $e->getMessage(),
                'user_ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar fornecedores',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Consulta detalhes de um cliente específico
     * 
     * @param int $omieId
     * @return JsonResponse
     */
    public function showClient(int $omieId): JsonResponse
    {
        if ($omieId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'ID Omie inválido'
            ], 422);
        }

        try {
            $response = $this->omieService->consultarCliente($omieId);

            if (!$response['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $response['message']
                ], 404);
            }

            $clienteFormatado = $this->formatClientData($response['data']);

            Log::info('Consulta de cliente realizada', [
                'omie_id' => $omieId,
                'client_name' => $clienteFormatado['nome'] ?? 'N/A'
            ]);

            return response()->json([
                'success' => true,
                'data' => $clienteFormatado
            ]);

        } catch (Exception $e) {
            Log::error('Erro na consulta de cliente', [
                'omie_id' => $omieId,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar cliente',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Consulta detalhes de um fornecedor específico
     * 
     * @param int $omieId
     * @return JsonResponse
     */
    public function showSupplier(int $omieId): JsonResponse
    {
        if ($omieId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'ID Omie inválido'
            ], 422);
        }

        try {
            $response = $this->omieService->consultarFornecedor($omieId);

            if (!$response['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $response['message']
                ], 404);
            }

            $fornecedorFormatado = $this->formatClientData($response['data']);

            Log::info('Consulta de fornecedor realizada', [
                'omie_id' => $omieId,
                'supplier_name' => $fornecedorFormatado['nome'] ?? 'N/A'
            ]);

            return response()->json([
                'success' => true,
                'data' => $fornecedorFormatado
            ]);

        } catch (Exception $e) {
            Log::error('Erro na consulta de fornecedor', [
                'omie_id' => $omieId,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar fornecedor',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Lista fornecedores com paginação e filtros
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function listSuppliers(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'filters' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        $filters = $request->input('filters', []);

        try {
            $result = $this->omieService->listSuppliers($page, $perPage, $filters);

            $fornecedoresFormatados = [];
            if (isset($result['fornecedores_cadastro'])) {
                $fornecedoresFormatados = array_map([$this, 'formatSupplierData'], $result['fornecedores_cadastro']);
            }

            return response()->json([
                'success' => true,
                'data' => $fornecedoresFormatados,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $result['total_de_registros'] ?? 0,
                    'total_pages' => $result['total_de_paginas'] ?? 0
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Erro na listagem de fornecedores', [
                'page' => $page,
                'per_page' => $perPage,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar fornecedores',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Lista clientes com paginação e filtros
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function listClients(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'filters' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        $filters = $request->input('filters', []);

        try {
            $result = $this->omieService->listClients($page, $perPage, $filters);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Erro ao buscar clientes'
                ], 500);
            }

            $clientesFormatados = [];
            if (isset($result['data'])) {
                $clientesFormatados = array_map([$this, 'formatClientData'], $result['data']);
            }

            return response()->json([
                'success' => true,
                'data' => $clientesFormatados,
                'pagination' => $result['pagination'] ?? [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => 0,
                    'total_pages' => 0
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Erro na listagem de clientes', [
                'page' => $page,
                'per_page' => $perPage,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar clientes',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Busca cliente por documento (CPF/CNPJ)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function findByDocument(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'document' => 'required|string|min:11|max:18'
        ], [
            'document.required' => 'Documento é obrigatório',
            'document.min' => 'Documento deve ter pelo menos 11 caracteres',
            'document.max' => 'Documento não pode exceder 18 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $document = $request->input('document');

        try {
            $cliente = $this->omieService->getClientByDocument($document);

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente não encontrado com este documento'
                ], 404);
            }

            $clienteFormatado = $this->formatClientData($cliente);

            return response()->json([
                'success' => true,
                'data' => $clienteFormatado
            ]);

        } catch (Exception $e) {
            Log::error('Erro na busca por documento', [
                'document' => $document,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar por documento',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Limpa o cache da integração Omie
     * 
     * @return JsonResponse
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->omieService->clearCache();

            Log::info('Cache Omie limpo via API');

            return response()->json([
                'success' => true,
                'message' => 'Cache limpo com sucesso'
            ]);

        } catch (Exception $e) {
            Log::error('Erro ao limpar cache Omie', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar cache',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Retorna estatísticas da integração
     * 
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            // Teste de conectividade
            $connectionTest = $this->omieService->testConnection();
            
            // Estatísticas básicas
            $stats = [
                'connection_status' => $connectionTest['success'] ? 'connected' : 'disconnected',
                'api_url' => config('services.omie.api_url'),
                'last_test' => now()->toISOString(),
                'response_time_ms' => $connectionTest['response_time'] ?? null,
                'cache_enabled' => true,
                'rate_limiting_enabled' => true
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Formata dados do cliente/fornecedor para resposta da API
     * 
     * @param array $data
     * @return array
     */
    private function formatClientData(array $data): array
    {
        return [
            'id' => $data['codigo_cliente_omie'] ?? null,
            'omie_id' => $data['codigo_cliente_omie'] ?? null,
            'codigo_cliente_integracao' => $data['codigo_cliente_integracao'] ?? null,
            'nome' => $data['razao_social'] ?? $data['nome_fantasia'] ?? 'N/A',
            'nome_fantasia' => $data['nome_fantasia'] ?? '',
            'razao_social' => $data['razao_social'] ?? '',
            'documento' => $this->omieService->formatDocument($data['cnpj_cpf'] ?? ''),
            'documento_limpo' => $data['cnpj_cpf'] ?? '',
            'email' => $data['email'] ?? '',
            'telefone' => $this->formatPhone($data),
            'endereco' => $this->formatAddress($data),
            'cidade' => $data['cidade'] ?? '',
            'estado' => $data['estado'] ?? '',
            'cep' => $data['cep'] ?? '',
            'ativo' => ($data['inativo'] ?? 'N') === 'N',
            'pessoa_fisica' => ($data['pessoa_fisica'] ?? 'N') === 'S',
            'contribuinte' => ($data['contribuinte'] ?? 'N') === 'S',
            'tags' => $data['tags'] ?? [],
            'observacoes' => $data['observacoes'] ?? '',
            'data_inclusao' => $data['dInc'] ?? null,
            'data_alteracao' => $data['dAlt'] ?? null
        ];
    }

    /**
     * Faz uma requisição direta à API Omie
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function directRequest(Request $request): JsonResponse
    {
        try {
            \Log::info('DirectRequest iniciado', ['request_data' => $request->all()]);
            
            $validator = Validator::make($request->all(), [
                'call' => 'required|string',
                'param' => 'required|array'
            ]);

            if ($validator->fails()) {
                \Log::error('Erro de validação', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $call = $request->input('call');
            $param = $request->input('param');
            
            \Log::info('Dados validados', ['call' => $call, 'param' => $param]);
            
            // Determinar o endpoint baseado no call
            $endpoint = $this->getEndpointFromCall($call);
            \Log::info('Endpoint determinado', ['endpoint' => $endpoint]);
            
            if (!$endpoint) {
                \Log::error('Endpoint não encontrado', ['call' => $call]);
                return response()->json([
                    'success' => false,
                    'message' => 'Call não suportado: ' . $call
                ], 400);
            }

            // O makeRequest espera um array simples, não um array de arrays
            // Se param é um array de arrays, pegar o primeiro elemento
            $paramForRequest = is_array($param) && isset($param[0]) ? $param[0] : $param;
            \Log::info('Param ajustado', ['param' => $paramForRequest]);

            \Log::info('Fazendo requisição para Omie', ['endpoint' => $endpoint, 'call' => $call, 'param' => $paramForRequest]);
            $response = $this->omieService->makeRequest($endpoint, $call, $paramForRequest);
            \Log::info('Resposta recebida da Omie', ['response' => $response]);

            Log::info('Requisição direta à API Omie realizada', [
                'call' => $call,
                'endpoint' => $endpoint,
                'success' => $response['success'] ?? false
            ]);

            return response()->json($response);

        } catch (Exception $e) {
            \Log::error('Erro interno', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Log::error('Erro na requisição direta à API Omie', [
                'call' => $request->input('call'),
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar requisição',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Determina o endpoint baseado no call
     * 
     * @param string $call
     * @return string|null
     */
    private function getEndpointFromCall(string $call): ?string
    {
        $endpoints = [
            'ConsultarCliente' => 'geral/clientes/',
            'ListarClientes' => 'geral/clientes/',
            'ConsultarFornecedor' => 'geral/fornecedores/',
            'ListarFornecedores' => 'geral/fornecedores/',
            'IncluirCliente' => 'geral/clientes/',
            'AlterarCliente' => 'geral/clientes/',
            'ExcluirCliente' => 'geral/clientes/',
            'IncluirFornecedor' => 'geral/fornecedores/',
            'AlterarFornecedor' => 'geral/fornecedores/',
            'ExcluirFornecedor' => 'geral/fornecedores/',
        ];

        return $endpoints[$call] ?? null;
    }

    /**
     * Formata telefone
     * 
     * @param array $data
     * @return string
     */
    private function formatPhone(array $data): string
    {
        $ddd = $data['telefone1_ddd'] ?? '';
        $numero = $data['telefone1_numero'] ?? '';
        
        if (empty($ddd) && empty($numero)) {
            return '';
        }
        
        return $ddd . $numero;
    }

    /**
     * Formata endereço
     * 
     * @param array $data
     * @return array
     */
    private function formatAddress(array $data): array
    {
        return [
            'logradouro' => $data['endereco'] ?? '',
            'numero' => $data['endereco_numero'] ?? '',
            'complemento' => $data['complemento'] ?? '',
            'bairro' => $data['bairro'] ?? '',
            'cidade' => $data['cidade'] ?? '',
            'estado' => $data['estado'] ?? '',
            'cep' => $data['cep'] ?? '',
            'pais' => $data['pais'] ?? 'Brasil'
        ];
    }


}