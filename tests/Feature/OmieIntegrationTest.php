<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\OmieService;
use Illuminate\Support\Facades\Config;

/**
 * Testes de integração para o OmieService
 * 
 * ATENÇÃO: Estes testes fazem chamadas reais para a API do Omie.
 * Execute apenas quando necessário e com credenciais válidas.
 * 
 * Para executar: php artisan test tests/Feature/OmieIntegrationTest.php --group=integration
 */
class OmieIntegrationTest extends TestCase
{
    private OmieService $omieService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Verifica se as credenciais estão configuradas
        if (empty(config('services.omie.app_key')) || empty(config('services.omie.app_secret'))) {
            $this->markTestSkipped('Credenciais do Omie não configuradas. Configure OMIE_APP_KEY e OMIE_APP_SECRET no .env');
        }
        
        $this->omieService = app(OmieService::class);
    }

    /**
     * @test
     * @group integration
     */
    public function test_real_api_connection()
    {
        // Act
        $result = $this->omieService->testConnection();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        
        if ($result['success']) {
            $this->assertArrayHasKey('data', $result);
            $this->assertArrayHasKey('response_time', $result);
            echo "\n✅ Conexão com API Omie estabelecida com sucesso!";
            echo "\n⏱️  Tempo de resposta: " . ($result['response_time'] ?? 0) . "ms";
        } else {
            echo "\n❌ Falha na conexão: " . $result['message'];
            echo "\n🔍 Verifique suas credenciais OMIE_APP_KEY e OMIE_APP_SECRET";
        }
    }

    /**
     * @test
     * @group integration
     */
    public function test_real_client_search()
    {
        // Act
        $result = $this->omieService->searchClients('teste', 5);

        // Assert
        $this->assertIsArray($result);
        echo "\n🔍 Busca por clientes retornou " . count($result) . " resultados";
        
        if (!empty($result)) {
            $firstClient = $result[0];
            $this->assertArrayHasKey('codigo_cliente_omie', $firstClient);
            echo "\n👤 Primeiro cliente: " . ($firstClient['razao_social'] ?? 'N/A');
        }
    }

    /**
     * @test
     * @group integration
     */
    public function test_real_supplier_search()
    {
        // Act
        $result = $this->omieService->searchSuppliers('teste', '', 5);

        // Assert
        $this->assertIsArray($result);
        echo "\n🏭 Busca por fornecedores retornou " . count($result) . " resultados";
    }

    /**
     * @test
     * @group integration
     */
    public function test_performance_benchmark()
    {
        $startTime = microtime(true);
        
        // Act - Múltiplas operações para testar performance
        $operations = [
            'testConnection' => fn() => $this->omieService->testConnection(),
            'searchClients' => fn() => $this->omieService->searchClients('teste', 3),
            'searchSuppliers' => fn() => $this->omieService->searchSuppliers('teste', '', 3),
        ];
        
        $results = [];
        foreach ($operations as $name => $operation) {
            $opStart = microtime(true);
            $results[$name] = $operation();
            $results[$name . '_time'] = (microtime(true) - $opStart) * 1000;
        }
        
        $totalTime = (microtime(true) - $startTime) * 1000;
        
        // Assert
        $this->assertLessThan(30000, $totalTime, 'Operações devem completar em menos de 30 segundos');
        
        echo "\n📊 Benchmark de Performance:";
        echo "\n   • Teste de Conexão: " . round($results['testConnection_time'], 2) . "ms";
        echo "\n   • Busca de Clientes: " . round($results['searchClients_time'], 2) . "ms";
        echo "\n   • Busca de Fornecedores: " . round($results['searchSuppliers_time'], 2) . "ms";
        echo "\n   • Tempo Total: " . round($totalTime, 2) . "ms";
    }

    /**
     * @test
     * @group integration
     */
    public function test_cache_effectiveness()
    {
        $searchTerm = 'teste_cache_' . time();
        
        // Primeira busca (sem cache)
        $start1 = microtime(true);
        $result1 = $this->omieService->searchClients($searchTerm, 5);
        $time1 = (microtime(true) - $start1) * 1000;
        
        // Segunda busca (com cache)
        $start2 = microtime(true);
        $result2 = $this->omieService->searchClients($searchTerm, 5);
        $time2 = (microtime(true) - $start2) * 1000;
        
        // Assert
        $this->assertEquals($result1, $result2, 'Resultados devem ser idênticos');
        $this->assertLessThan($time1, $time2, 'Segunda busca deve ser mais rápida (cache)');
        
        echo "\n💾 Teste de Cache:";
        echo "\n   • Primeira busca: " . round($time1, 2) . "ms";
        echo "\n   • Segunda busca (cache): " . round($time2, 2) . "ms";
        echo "\n   • Melhoria: " . round((($time1 - $time2) / $time1) * 100, 1) . "%";
    }

    /**
     * @test
     * @group integration
     */
    public function test_error_handling()
    {
        // Simula uma busca que deve retornar erro ou resultado vazio
        $result = $this->omieService->searchClients('cliente_inexistente_' . time());
        
        // Assert
        $this->assertIsArray($result);
        echo "\n🚫 Busca por cliente inexistente: " . (empty($result) ? 'Vazio (correto)' : count($result) . ' resultados');
    }
}