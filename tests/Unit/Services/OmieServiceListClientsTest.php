<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\OmieService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class OmieServiceListClientsTest extends TestCase
{
    private OmieService $omieService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar variáveis de ambiente para teste
        Config::set('services.omie.app_key', 'test_app_key');
        Config::set('services.omie.app_secret', 'test_app_secret');
        Config::set('services.omie.api_url', 'https://app.omie.com.br/api/v1/');
        
        $this->omieService = new OmieService();
        
        // Limpar cache antes de cada teste
        Cache::flush();
    }

    public function test_list_clients_success_with_default_parameters()
    {
        // Simular resposta da API
        $mockResponse = [
            'clientes_cadastro' => [
                [
                    'codigo_cliente_omie' => 123456,
                    'codigo_cliente_integracao' => 'CLI001',
                    'razao_social' => 'Empresa Teste LTDA',
                    'nome_fantasia' => 'Empresa Teste',
                    'cnpj_cpf' => '12345678000195',
                    'email' => 'contato@empresateste.com',
                    'telefone1_ddd' => '11',
                    'telefone1_numero' => '999999999',
                    'endereco' => 'Rua Teste',
                    'endereco_numero' => '123',
                    'bairro' => 'Centro',
                    'cidade' => 'São Paulo',
                    'estado' => 'SP',
                    'cep' => '01000000',
                    'inscricao_estadual' => '123456789',
                    'inscricao_municipal' => '987654321',
                    'optante_simples_nacional' => 'S',
                    'inativo' => 'N',
                    'data_inclusao' => '01/01/2024',
                    'data_alteracao' => '15/01/2024'
                ]
            ],
            'total_de_registros' => 1,
            'total_de_paginas' => 1
        ];

        Http::fake([
            'app.omie.com.br/api/v1/geral/clientes/' => Http::response($mockResponse, 200)
        ]);

        $result = $this->omieService->listClients();

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals(123456, $result['data'][0]['codigo_cliente_omie']);
        $this->assertEquals('CLI001', $result['data'][0]['codigo_cliente_integracao']);
        $this->assertEquals('Empresa Teste LTDA', $result['data'][0]['razao_social']);
        $this->assertEquals('12.345.678/0001-95', $result['data'][0]['cnpj_cpf_formatado']);
        $this->assertEquals('(11) 999999999', $result['data'][0]['telefone_completo']);
        $this->assertStringContainsString('Rua Teste, 123', $result['data'][0]['endereco_completo']);
        
        // Verificar paginação
        $this->assertEquals(1, $result['pagination']['current_page']);
        $this->assertEquals(50, $result['pagination']['per_page']);
        $this->assertEquals(1, $result['pagination']['total']);
        $this->assertEquals(1, $result['pagination']['total_pages']);
        $this->assertFalse($result['pagination']['has_next_page']);
        $this->assertFalse($result['pagination']['has_previous_page']);
    }

    public function test_list_clients_with_custom_pagination()
    {
        $mockResponse = [
            'clientes_cadastro' => [],
            'total_de_registros' => 150,
            'total_de_paginas' => 6
        ];

        Http::fake([
            'app.omie.com.br/api/v1/geral/clientes/' => Http::response($mockResponse, 200)
        ]);

        $result = $this->omieService->listClients(2, 25);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['pagination']['current_page']);
        $this->assertEquals(25, $result['pagination']['per_page']);
        $this->assertEquals(150, $result['pagination']['total']);
        $this->assertEquals(6, $result['pagination']['total_pages']);
        $this->assertTrue($result['pagination']['has_next_page']);
        $this->assertTrue($result['pagination']['has_previous_page']);
    }

    public function test_list_clients_with_filters()
    {
        $mockResponse = [
            'clientes_cadastro' => [
                [
                    'codigo_cliente_omie' => 789,
                    'codigo_cliente_integracao' => 'CLI002',
                    'razao_social' => 'Cliente Filtrado LTDA',
                    'cnpj_cpf' => '98765432000123',
                    'email' => 'filtrado@teste.com'
                ]
            ],
            'total_de_registros' => 1,
            'total_de_paginas' => 1
        ];

        Http::fake([
            'app.omie.com.br/api/v1/geral/clientes/' => Http::response($mockResponse, 200)
        ]);

        $filters = [
            'codigo_cliente_integracao' => 'CLI002',
            'razao_social' => 'Cliente Filtrado',
            'cnpj_cpf' => '98.765.432/0001-23',
            'email' => 'filtrado@teste.com',
            'cidade' => 'Rio de Janeiro',
            'uf' => 'RJ',
            'apenas_importado_api' => 'S'
        ];

        $result = $this->omieService->listClients(1, 50, $filters);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['filters_applied']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals('CLI002', $result['data'][0]['codigo_cliente_integracao']);
    }

    public function test_list_clients_parameter_validation()
    {
        $mockResponse = [
            'clientes_cadastro' => [],
            'total_de_registros' => 0,
            'total_de_paginas' => 0
        ];

        Http::fake([
            'app.omie.com.br/api/v1/geral/clientes/' => Http::response($mockResponse, 200)
        ]);

        // Testar validação de página negativa
        $result = $this->omieService->listClients(-1, 50);
        $this->assertEquals(1, $result['pagination']['current_page']);

        // Testar validação de registros por página acima do limite
        $result = $this->omieService->listClients(1, 1000);
        $this->assertEquals(500, $result['pagination']['per_page']);

        // Testar validação de registros por página zero
        $result = $this->omieService->listClients(1, 0);
        $this->assertEquals(1, $result['pagination']['per_page']);
    }

    public function test_list_clients_api_error_response()
    {
        Http::fake([
            'app.omie.com.br/api/v1/geral/clientes/' => Http::response([
                'faultstring' => 'Erro na API'
            ], 500)
        ]);

        $result = $this->omieService->listClients();

        $this->assertFalse($result['success']);
        $this->assertEmpty($result['data']);
        $this->assertEquals(1, $result['pagination']['current_page']);
        $this->assertEquals(50, $result['pagination']['per_page']);
        $this->assertEquals(0, $result['pagination']['total']);
    }

    public function test_list_clients_network_exception()
    {
        Http::fake([
            'app.omie.com.br/api/v1/geral/clientes/' => function () {
                throw new \Exception('Erro de conexão');
            }
        ]);

        $result = $this->omieService->listClients();

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Erro', $result['message']);
        $this->assertEmpty($result['data']);
    }

    public function test_list_clients_caching()
    {
        $mockResponse = [
            'clientes_cadastro' => [
                [
                    'codigo_cliente_omie' => 999,
                    'codigo_cliente_integracao' => 'CACHE_TEST',
                    'razao_social' => 'Teste Cache LTDA'
                ]
            ],
            'total_de_registros' => 1,
            'total_de_paginas' => 1
        ];

        Http::fake([
            'app.omie.com.br/api/v1/geral/clientes/' => Http::response($mockResponse, 200)
        ]);

        // Primeira chamada - deve fazer requisição HTTP
        $result1 = $this->omieService->listClients(1, 50, ['test' => 'cache']);
        $this->assertTrue($result1['success']);

        // Segunda chamada com os mesmos parâmetros - deve usar cache
        Http::fake([]); // Não permitir novas requisições HTTP
        
        $result2 = $this->omieService->listClients(1, 50, ['test' => 'cache']);
        $this->assertTrue($result2['success']);
        $this->assertEquals($result1['data'], $result2['data']);
    }

    public function test_list_clients_empty_response()
    {
        $mockResponse = [
            'clientes_cadastro' => [],
            'total_de_registros' => 0,
            'total_de_paginas' => 0
        ];

        Http::fake([
            'app.omie.com.br/api/v1/geral/clientes/' => Http::response($mockResponse, 200)
        ]);

        $result = $this->omieService->listClients();

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['data']);
        $this->assertEquals(0, $result['pagination']['total']);
        $this->assertEquals(0, $result['pagination']['total_pages']);
    }

    public function test_prepare_client_filters()
    {
        // Usar reflexão para testar método privado
        $reflection = new \ReflectionClass($this->omieService);
        $method = $reflection->getMethod('prepareClientFilters');
        $method->setAccessible(true);

        $filters = [
            'codigo_cliente_integracao' => 'CLI001',
            'codigo_cliente_omie' => 123,
            'cnpj_cpf' => '12.345.678/0001-95',
            'email' => 'test@test.com',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'data_alteracao_de' => '01/01/2024',
            'data_alteracao_ate' => '31/01/2024',
            'apenas_importado_api' => 'S',
            'inativo' => 'S',
            'campo_inexistente' => 'valor'
        ];

        $result = $method->invoke($this->omieService, $filters);

        $this->assertEquals('CLI001', $result['codigo_cliente_integracao']);
        $this->assertEquals(123, $result['codigo_cliente_omie']);
        $this->assertEquals('12345678000195', $result['cnpj_cpf']); // Documento limpo
        $this->assertEquals('test@test.com', $result['email']);
        $this->assertEquals('São Paulo', $result['cidade']);
        $this->assertEquals('SP', $result['uf']);
        $this->assertEquals('01/01/2024', $result['data_alteracao_de']);
        $this->assertEquals('31/01/2024', $result['data_alteracao_ate']);
        $this->assertEquals('S', $result['apenas_importado_api']);
        $this->assertEquals('S', $result['inativo']);
        $this->assertArrayNotHasKey('campo_inexistente', $result);
    }

    public function test_process_clients_list()
    {
        // Usar reflexão para testar método privado
        $reflection = new \ReflectionClass($this->omieService);
        $method = $reflection->getMethod('processClientsList');
        $method->setAccessible(true);

        $clients = [
            [
                'codigo_cliente_omie' => 123,
                'codigo_cliente_integracao' => 'CLI001',
                'razao_social' => 'Teste LTDA',
                'nome_fantasia' => 'Teste',
                'cnpj_cpf' => '12345678000195',
                'email' => 'test@test.com',
                'telefone1_ddd' => '11',
                'telefone1_numero' => '999999999',
                'endereco' => 'Rua Teste',
                'endereco_numero' => '123',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01000000'
            ]
        ];

        $result = $method->invoke($this->omieService, $clients);

        $this->assertCount(1, $result);
        $client = $result[0];
        
        $this->assertEquals(123, $client['codigo_cliente_omie']);
        $this->assertEquals('CLI001', $client['codigo_cliente_integracao']);
        $this->assertEquals('Teste LTDA', $client['razao_social']);
        $this->assertEquals('12.345.678/0001-95', $client['cnpj_cpf_formatado']);
        $this->assertEquals('(11) 999999999', $client['telefone_completo']);
        $this->assertStringContainsString('Rua Teste, 123', $client['endereco_completo']);
        $this->assertArrayHasKey('dados_originais', $client);
    }

    public function test_listar_clientes_compatibility_method()
    {
        $mockResponse = [
            'clientes_cadastro' => [
                [
                    'codigo_cliente_omie' => 456,
                    'codigo_cliente_integracao' => 'COMPAT_TEST',
                    'razao_social' => 'Compatibilidade LTDA'
                ]
            ],
            'total_de_registros' => 1,
            'total_de_paginas' => 1
        ];

        Http::fake([
            'app.omie.com.br/api/v1/geral/clientes/' => Http::response($mockResponse, 200)
        ]);

        $filtros = [
            'pagina' => 2,
            'registros_por_pagina' => 25,
            'apenas_importado_api' => 'S'
        ];

        $result = $this->omieService->listarClientes($filtros);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['pagination']['current_page']);
        $this->assertEquals(25, $result['pagination']['per_page']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals('COMPAT_TEST', $result['data'][0]['codigo_cliente_integracao']);
    }
}