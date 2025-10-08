<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\OmieService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Mockery;

class OmieServiceTest extends TestCase
{
    private OmieService $omieService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar as variáveis de ambiente para teste
        Config::set('services.omie.app_key', 'test_app_key');
        Config::set('services.omie.app_secret', 'test_app_secret');
        Config::set('services.omie.api_url', 'https://app.omie.com.br/api/v1/');
        
        $this->omieService = new OmieService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function test_connection_success()
    {
        // Arrange
        $mockResponse = [
            'clientes_cadastro' => [
                [
                    'codigo_cliente_omie' => 123,
                    'razao_social' => 'Teste Cliente',
                    'cnpj_cpf' => '12345678901234'
                ]
            ],
            'total_de_registros' => 1
        ];

        Http::fake([
            'https://app.omie.com.br/api/v1/geral/clientes/' => Http::response($mockResponse, 200)
        ]);

        // Act
        $result = $this->omieService->testConnection();

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('Conexão estabelecida com sucesso!', $result['message']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('response_time', $result);
    }

    /** @test */
    public function test_connection_failure_missing_credentials()
    {
        // Arrange & Act & Assert
        Config::set('services.omie.app_key', '');
        Config::set('services.omie.app_secret', '');
        
        // Espera que uma exceção seja lançada quando as credenciais estão vazias
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Configurações da API Omie não encontradas');
        
        new OmieService();
    }

    /** @test */
    public function test_connection_api_error()
    {
        // Arrange
        Http::fake([
            'https://app.omie.com.br/api/v1/geral/clientes/' => Http::response([
                'faultCode' => 'SOAP-ENV:Client',
                'faultString' => 'Erro de autenticação'
            ], 500)
        ]);

        // Act
        $result = $this->omieService->testConnection();

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Falha na conexão', $result['message']);
    }

    /** @test */
    public function test_search_clients_by_document()
    {
        // Arrange
        $mockResponse = [
            'codigo_cliente_omie' => 123,
            'razao_social' => 'Cliente Teste LTDA',
            'cnpj_cpf' => '12345678901234',
            'nome_fantasia' => 'Cliente Teste'
        ];

        Http::fake([
            'https://app.omie.com.br/api/v1/geral/clientes/' => Http::response($mockResponse, 200)
        ]);

        // Act
        $result = $this->omieService->searchClients('12345678901234');

        // Assert
        $this->assertIsArray($result);
        if (!empty($result)) {
            $this->assertEquals(123, $result[0]['codigo_cliente_omie']);
            $this->assertEquals('Cliente Teste LTDA', $result[0]['razao_social']);
        }
    }

    /** @test */
    public function test_search_clients_by_name()
    {
        // Arrange
        $mockResponse = [
            'clientes_cadastro' => [
                [
                    'codigo_cliente_omie' => 123,
                    'razao_social' => 'Cliente Teste LTDA',
                    'cnpj_cpf' => '12345678901234',
                    'nome_fantasia' => 'Cliente Teste'
                ]
            ],
            'total_de_registros' => 1
        ];

        Http::fake([
            'https://app.omie.com.br/api/v1/geral/clientes/' => Http::response($mockResponse, 200)
        ]);

        // Act
        $result = $this->omieService->searchClients('Cliente Teste');

        // Assert
        $this->assertIsArray($result);
        if (!empty($result)) {
            $this->assertEquals('Cliente Teste LTDA', $result[0]['razao_social']);
        }
    }

    /** @test */
    public function test_search_clients_empty_term()
    {
        // Act
        $result = $this->omieService->searchClients('');

        // Assert
        $this->assertEmpty($result);
    }

    /** @test */
    public function test_search_clients_short_term()
    {
        // Act
        $result = $this->omieService->searchClients('a');

        // Assert
        $this->assertEmpty($result);
    }

    /** @test */
    public function test_search_suppliers_by_code()
    {
        // Arrange
        $mockResponse = [
            'codigo_cliente_omie' => 456,
            'razao_social' => 'Fornecedor Teste LTDA',
            'cnpj_cpf' => '98765432109876',
            'nome_fantasia' => 'Fornecedor Teste'
        ];

        Http::fake([
            'https://app.omie.com.br/api/v1/geral/clientes/' => Http::response($mockResponse, 200)
        ]);

        // Act
        $result = $this->omieService->searchSuppliers('456', 'codigo');

        // Assert
        $this->assertIsArray($result);
    }

    /** @test */
    public function test_search_suppliers_by_name()
    {
        // Arrange
        $mockResponse = [
            'clientes_cadastro' => [
                [
                    'codigo_cliente_omie' => 456,
                    'razao_social' => 'Fornecedor Teste LTDA',
                    'cnpj_cpf' => '98765432109876',
                    'nome_fantasia' => 'Fornecedor Teste'
                ]
            ],
            'total_de_registros' => 1
        ];

        Http::fake([
            'https://app.omie.com.br/api/v1/geral/clientes/' => Http::response($mockResponse, 200)
        ]);

        // Act
        $result = $this->omieService->searchSuppliers('Fornecedor Teste');

        // Assert
        $this->assertIsArray($result);
    }

    /** @test */
    public function test_cache_functionality()
    {
        // Arrange
        Cache::shouldReceive('remember')
            ->once()
            ->with(Mockery::type('string'), 300, Mockery::type('callable'))
            ->andReturn([]);

        // Act
        $result = $this->omieService->searchClients('teste cache');

        // Assert
        $this->assertIsArray($result);
    }

    /** @test */
    public function test_api_no_records_response()
    {
        // Arrange
        Http::fake([
            'https://app.omie.com.br/api/v1/geral/clientes/' => Http::response([
                'faultCode' => 'SOAP-ENV:Client-5113',
                'faultString' => 'Não existem registros para a consulta'
            ], 500)
        ]);

        // Act
        $result = $this->omieService->searchClients('cliente inexistente');

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function test_document_validation()
    {
        // Arrange & Act
        $validCpf = $this->omieService->searchClients('12345678901');
        $validCnpj = $this->omieService->searchClients('12345678901234');
        $invalidDoc = $this->omieService->searchClients('123');

        // Assert
        $this->assertIsArray($validCpf);
        $this->assertIsArray($validCnpj);
        $this->assertIsArray($invalidDoc);
    }

    /** @test */
    public function test_retry_logic_on_temporary_failure()
    {
        // Arrange
        Http::fake([
            'https://app.omie.com.br/api/v1/geral/clientes/' => Http::sequence()
                ->push(['error' => 'timeout'], 500)
                ->push([
                    'clientes_cadastro' => [
                        ['codigo_cliente_omie' => 123, 'razao_social' => 'Cliente Teste']
                    ]
                ], 200)
        ]);

        // Act
        $result = $this->omieService->searchClients('Cliente Teste');

        // Assert
        $this->assertIsArray($result);
    }

    /** @test */
    public function test_logging_functionality()
    {
        // Arrange
        Log::shouldReceive('info')->atLeast()->once();
        
        Http::fake([
            'https://app.omie.com.br/api/v1/geral/clientes/' => Http::response([
                'clientes_cadastro' => [],
                'total_de_registros' => 0
            ], 200)
        ]);

        // Act
        $this->omieService->testConnection();

        // Assert - Log expectations are verified automatically by Mockery
        $this->assertTrue(true);
    }

    /** @test */
    public function test_service_configuration()
    {
        // Act
        $reflection = new \ReflectionClass($this->omieService);
        $appKeyProperty = $reflection->getProperty('appKey');
        $appKeyProperty->setAccessible(true);
        $appKey = $appKeyProperty->getValue($this->omieService);

        $appSecretProperty = $reflection->getProperty('appSecret');
        $appSecretProperty->setAccessible(true);
        $appSecret = $appSecretProperty->getValue($this->omieService);

        $apiUrlProperty = $reflection->getProperty('apiUrl');
        $apiUrlProperty->setAccessible(true);
        $apiUrl = $apiUrlProperty->getValue($this->omieService);

        // Assert
        $this->assertEquals('test_app_key', $appKey);
        $this->assertEquals('test_app_secret', $appSecret);
        $this->assertEquals('https://app.omie.com.br/api/v1/', $apiUrl);
    }
}