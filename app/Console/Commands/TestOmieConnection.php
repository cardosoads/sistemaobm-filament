<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OmieService;
use Illuminate\Support\Facades\Http;

class TestOmieConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omie:test {--raw : Fazer requisição HTTP direta}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a conexão e dados da API Omie';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testando conexão com API Omie...');
        
        if ($this->option('raw')) {
            $this->testRawConnection();
        } else {
            $this->testServiceConnection();
        }
    }
    
    private function testServiceConnection()
    {
        $omieService = app(OmieService::class);
        
        // Teste 1: Verificar conexão
        $this->info('📡 Testando conexão básica...');
        $connectionTest = $omieService->testConnection();
        
        if ($connectionTest['success']) {
            $this->info('✅ Conexão estabelecida com sucesso!');
        } else {
            $this->error('❌ Falha na conexão: ' . $connectionTest['message']);
            return;
        }
        
        // Teste 2: Listar clientes
        $this->info('👥 Testando listagem de clientes...');
        $clientsResult = $omieService->listClients(1, 10);
        
        if ($clientsResult['success']) {
            $clients = $clientsResult['data'];
            $total = $clientsResult['pagination']['total'] ?? 0;
            
            $this->info("✅ Clientes encontrados: {$total}");
            
            if (!empty($clients)) {
                $this->info('📋 Primeiros clientes:');
                foreach (array_slice($clients, 0, 3) as $client) {
                    $this->line("  - {$client['razao_social']} (Código: {$client['codigo_cliente_omie']})");
                }
            } else {
                $this->warn('⚠️  Nenhum cliente retornado na listagem');
            }
        } else {
            $this->error('❌ Erro ao listar clientes: ' . $clientsResult['message']);
        }
        
        // Teste 3: Listar fornecedores
        $this->info('🏭 Testando listagem de fornecedores...');
        $suppliersResult = $omieService->listSuppliers(1, 10);
        
        if ($suppliersResult['success']) {
            $suppliers = $suppliersResult['data'];
            $total = $suppliersResult['pagination']['total'] ?? 0;
            
            $this->info("✅ Fornecedores encontrados: {$total}");
            
            if (!empty($suppliers)) {
                $this->info('📋 Primeiros fornecedores:');
                foreach (array_slice($suppliers, 0, 3) as $supplier) {
                    $this->line("  - {$supplier['razao_social']} (Código: {$supplier['codigo']})");
                }
            } else {
                $this->warn('⚠️  Nenhum fornecedor retornado na listagem');
            }
        } else {
            $this->error('❌ Erro ao listar fornecedores: ' . $suppliersResult['message']);
        }
    }
    
    private function testRawConnection()
    {
        $this->info('🔧 Fazendo requisição HTTP direta...');
        
        $appKey = config('omie.app_key');
        $appSecret = config('omie.app_secret');
        
        if (empty($appKey) || empty($appSecret)) {
            $this->error('❌ Credenciais da API não configuradas');
            return;
        }
        
        $this->info("🔑 App Key: " . substr($appKey, 0, 10) . '...');
        $this->info("🔐 App Secret: " . substr($appSecret, 0, 10) . '...');
        
        try {
            $response = Http::timeout(30)->post('https://app.omie.com.br/api/v1/geral/clientes/', [
                'call' => 'ListarClientes',
                'app_key' => $appKey,
                'app_secret' => $appSecret,
                'param' => [
                    'pagina' => 1,
                    'registros_por_pagina' => 5,
                    'apenas_importado_api' => 'N'
                ]
            ]);
            
            $this->info('📡 Status HTTP: ' . $response->status());
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['clientes_cadastro'])) {
                    $clients = $data['clientes_cadastro'];
                    $total = $data['total_de_registros'] ?? count($clients);
                    
                    $this->info("✅ Resposta da API recebida com sucesso!");
                    $this->info("📊 Total de registros: {$total}");
                    $this->info("📋 Clientes na página: " . count($clients));
                    
                    if (!empty($clients)) {
                        $this->info('👥 Primeiros clientes:');
                        foreach (array_slice($clients, 0, 3) as $client) {
                            $razao = $client['razao_social'] ?? 'N/A';
                            $codigo = $client['codigo_cliente_omie'] ?? 'N/A';
                            $this->line("  - {$razao} (Código: {$codigo})");
                        }
                    }
                } else {
                    $this->warn('⚠️  Estrutura de resposta inesperada');
                    $this->line('Resposta: ' . json_encode($data, JSON_PRETTY_PRINT));
                }
            } else {
                $this->error('❌ Erro HTTP: ' . $response->status());
                $this->error('Resposta: ' . $response->body());
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Exceção: ' . $e->getMessage());
        }
    }
}
