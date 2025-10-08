<?php

namespace App\Console\Commands;

use App\Models\ClienteFornecedor;
use App\Services\OmieService;
use App\Services\CentroCustoSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SyncOmieData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omie:sync {--type=all : Tipo de sincronização (clientes, fornecedores, centros-custo, all)} {--force : Força sincronização mesmo com dados existentes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza dados de clientes, fornecedores e centros de custo com a API Omie';

    private array $stats = [
        'clientes_novos' => 0,
        'clientes_atualizados' => 0,
        'fornecedores_novos' => 0,
        'fornecedores_atualizados' => 0,
        'centros_custo_novos' => 0,
        'centros_custo_atualizados' => 0,
        'removidos' => 0,
        'erros' => 0
    ];

    private OmieService $omieService;
    private CentroCustoSyncService $centroCustoSyncService;

    public function __construct(OmieService $omieService, CentroCustoSyncService $centroCustoSyncService)
    {
        parent::__construct();
        $this->omieService = $omieService;
        $this->centroCustoSyncService = $centroCustoSyncService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando sincronização com API Omie...');
        
        try {
            // Testa conexão primeiro
            $this->info('🔍 Testando conexão com API Omie...');
            $connectionTest = $this->omieService->testConnection();
            
            if (!$connectionTest['success']) {
                $this->error('❌ Falha na conexão com API Omie: ' . $connectionTest['message']);
                return 1;
            }
            
            $this->info('✅ Conexão estabelecida com sucesso!');
            
            $type = $this->option('type');
            $force = $this->option('force');
            
            DB::beginTransaction();
            
            try {
                if ($type === 'all' || $type === 'clientes') {
                    $this->syncClientes($force);
                }
                
                if ($type === 'all' || $type === 'fornecedores') {
                    $this->syncFornecedores($force);
                }
                
                if ($type === 'all' || $type === 'centros-custo') {
                    $this->syncCentrosCusto($force);
                }
                
                // Remove registros que não existem mais na API
                if (!$force) {
                    $this->removeDeletedRecords();
                }
                
                DB::commit();
                
                $this->displayStats();
                $this->info('✅ Sincronização concluída com sucesso!');
                
                return 0;
                
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            $this->error('❌ Erro durante sincronização: ' . $e->getMessage());
            Log::error('Erro na sincronização Omie', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }

    /**
     * Sincroniza clientes da API Omie
     */
    private function syncClientes(bool $force = false): void
    {
        $this->info('📥 Sincronizando clientes...');
        
        $page = 1;
        $perPage = 50;
        $totalProcessed = 0;
        
        do {
            $this->info("   Processando página {$page}...");
            
            $response = $this->omieService->listClients($page, $perPage);
            
            if (empty($response['clientes_cadastro'])) {
                break;
            }
            
            $clientes = $response['clientes_cadastro'];
            
            foreach ($clientes as $clienteData) {
                try {
                    $this->processCliente($clienteData, $force);
                    $totalProcessed++;
                } catch (Exception $e) {
                    $this->stats['erros']++;
                    $this->warn("   ⚠️  Erro ao processar cliente {$clienteData['codigo_cliente_omie']}: " . $e->getMessage());
                }
            }
            
            $page++;
            
        } while (count($clientes) === $perPage);
        
        $this->info("   ✅ {$totalProcessed} clientes processados");
    }

    /**
     * Sincroniza fornecedores da API Omie
     */
    private function syncFornecedores(bool $force = false): void
    {
        $this->info('📥 Sincronizando fornecedores...');
        
        $page = 1;
        $perPage = 50;
        $totalProcessed = 0;
        
        do {
            $this->info("   Processando página {$page}...");
            
            $response = $this->omieService->listSuppliers($page, $perPage);
            
            if (empty($response['clientes_cadastro'])) {
                break;
            }
            
            $fornecedores = $response['clientes_cadastro'];
            
            foreach ($fornecedores as $fornecedorData) {
                try {
                    $this->processFornecedor($fornecedorData, $force);
                    $totalProcessed++;
                } catch (Exception $e) {
                    $this->stats['erros']++;
                    $this->warn("   ⚠️  Erro ao processar fornecedor {$fornecedorData['codigo_cliente_omie']}: " . $e->getMessage());
                }
            }
            
            $page++;
            
        } while (count($fornecedores) === $perPage);
        
        $this->info("   ✅ {$totalProcessed} fornecedores processados");
    }

    /**
     * Processa um cliente individual
     */
    private function processCliente(array $clienteData, bool $force = false): void
    {
        $omieId = $clienteData['codigo_cliente_omie'];
        
        $existing = ClienteFornecedor::where('codigo_cliente_omie', $omieId)
            ->where('is_cliente', true)
            ->first();
        
        $data = $this->mapClienteData($clienteData, true);
        
        if ($existing) {
            if ($force || $this->shouldUpdate($existing, $clienteData)) {
                $existing->update($data);
                $this->stats['clientes_atualizados']++;
                $this->line("   ↻ Cliente atualizado: {$data['razao_social']}");
            }
        } else {
            ClienteFornecedor::create($data);
            $this->stats['clientes_novos']++;
            $this->line("   + Cliente criado: {$data['razao_social']}");
        }
    }

    /**
     * Processa um fornecedor individual
     */
    private function processFornecedor(array $fornecedorData, bool $force = false): void
    {
        $omieId = $fornecedorData['codigo_cliente_omie'];
        
        $existing = ClienteFornecedor::where('codigo_cliente_omie', $omieId)
            ->where('is_cliente', false)
            ->first();
        
        $data = $this->mapClienteData($fornecedorData, false);
        
        if ($existing) {
            if ($force || $this->shouldUpdate($existing, $fornecedorData)) {
                $existing->update($data);
                $this->stats['fornecedores_atualizados']++;
                $this->line("   ↻ Fornecedor atualizado: {$data['razao_social']}");
            }
        } else {
            ClienteFornecedor::create($data);
            $this->stats['fornecedores_novos']++;
            $this->line("   + Fornecedor criado: {$data['razao_social']}");
        }
    }

    /**
     * Mapeia dados da API para o modelo local
     */
    private function mapClienteData(array $apiData, bool $isCliente): array
    {
        return [
            'is_cliente' => $isCliente,
            'codigo_cliente_omie' => $apiData['codigo_cliente_omie'],
            'codigo_cliente_integracao' => $apiData['codigo_cliente_integracao'] ?? null,
            'razao_social' => $apiData['razao_social'] ?? '',
            'nome_fantasia' => $apiData['nome_fantasia'] ?? '',
            'cnpj_cpf' => $apiData['cnpj_cpf'] ?? '',
            'email' => $apiData['email'] ?? '',
            'homepage' => $apiData['homepage'] ?? '',
            'telefone1_ddd' => $apiData['telefone1_ddd'] ?? '',
            'telefone1_numero' => $apiData['telefone1_numero'] ?? '',
            'telefone2_ddd' => $apiData['telefone2_ddd'] ?? '',
            'telefone2_numero' => $apiData['telefone2_numero'] ?? '',
            'fax_ddd' => $apiData['fax_ddd'] ?? '',
            'fax_numero' => $apiData['fax_numero'] ?? '',
            'endereco' => $apiData['endereco'] ?? '',
            'endereco_numero' => $apiData['endereco_numero'] ?? '',
            'complemento' => $apiData['complemento'] ?? '',
            'bairro' => $apiData['bairro'] ?? '',
            'cidade' => $apiData['cidade'] ?? '',
            'estado' => $apiData['estado'] ?? '',
            'cep' => $apiData['cep'] ?? '',
            'inativo' => $apiData['inativo'] ?? 'N',
            'inscricao_estadual' => $apiData['inscricao_estadual'] ?? '',
            'inscricao_municipal' => $apiData['inscricao_municipal'] ?? '',
            'pessoa_fisica' => $apiData['pessoa_fisica'] ?? 'N',
            'optante_simples_nacional' => $apiData['optante_simples_nacional'] ?? 'N',
            'contribuinte' => $apiData['contribuinte'] ?? 'N',
            'exterior' => $apiData['exterior'] ?? 'N',
            'importado_api' => 'S',
            'observacoes' => $apiData['observacoes'] ?? '',
            'obs_detalhadas' => $apiData['obs_detalhadas'] ?? '',
            'recomendacao_atraso' => $apiData['recomendacao_atraso'] ?? '',
            'dados_originais_api' => $apiData,
            'ultima_sincronizacao' => now(),
            'status_sincronizacao' => 'sincronizado',
            'data_inclusao' => isset($apiData['data_inclusao']) ? \Carbon\Carbon::parse($apiData['data_inclusao']) : null,
            'data_alteracao' => isset($apiData['data_alteracao']) ? \Carbon\Carbon::parse($apiData['data_alteracao']) : null,
        ];
    }

    /**
     * Verifica se um registro deve ser atualizado
     */
    private function shouldUpdate(ClienteFornecedor $existing, array $apiData): bool
    {
        // Verifica se houve alteração na API
        if (isset($apiData['data_alteracao']) && $existing->data_alteracao) {
            $apiDate = \Carbon\Carbon::parse($apiData['data_alteracao']);
            return $apiDate->gt($existing->data_alteracao);
        }
        
        // Se não há data de alteração, verifica se foi sincronizado há mais de 1 hora
        return $existing->ultima_sincronizacao === null || 
               $existing->ultima_sincronizacao->lt(now()->subHour());
    }

    /**
     * Sincroniza centros de custo com a API Omie
     */
    private function syncCentrosCusto(bool $force = false): void
    {
        $this->info('🏢 Sincronizando centros de custo...');
        
        try {
            $result = $this->centroCustoSyncService->syncTodos($force);
            
            $this->stats['centros_custo_novos'] = $result['novos'] ?? 0;
            $this->stats['centros_custo_atualizados'] = $result['atualizados'] ?? 0;
            
            $total = $this->stats['centros_custo_novos'] + $this->stats['centros_custo_atualizados'];
            $this->info("   ✅ {$total} centros de custo processados");
            $this->info("   📈 {$this->stats['centros_custo_novos']} novos");
            $this->info("   ↻ {$this->stats['centros_custo_atualizados']} atualizados");
            
        } catch (\Exception $e) {
            $this->stats['erros']++;
            $this->error("   ❌ Erro ao sincronizar centros de custo: {$e->getMessage()}");
        }
    }

    /**
     * Remove registros que não existem mais na API
     */
    private function removeDeletedRecords(): void
    {
        $this->info('🗑️  Verificando registros removidos...');
        
        // Marca como inativo registros que não foram sincronizados recentemente
        $cutoffDate = now()->subDays(7);
        
        $deleted = ClienteFornecedor::where('importado_api', 'S')
            ->where('ultima_sincronizacao', '<', $cutoffDate)
            ->where('inativo', 'N')
            ->update([
                'inativo' => 'S',
                'status_sincronizacao' => 'removido',
                'updated_at' => now()
            ]);
        
        if ($deleted > 0) {
            $this->stats['removidos'] = $deleted;
            $this->info("   🗑️  {$deleted} registros marcados como inativos");
        }
    }

    /**
     * Exibe estatísticas da sincronização
     */
    private function displayStats(): void
    {
        $this->info('');
        $this->info('📊 Estatísticas da Sincronização:');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("   📈 Clientes novos: {$this->stats['clientes_novos']}");
        $this->info("   ↻ Clientes atualizados: {$this->stats['clientes_atualizados']}");
        $this->info("   📈 Fornecedores novos: {$this->stats['fornecedores_novos']}");
        $this->info("   ↻ Fornecedores atualizados: {$this->stats['fornecedores_atualizados']}");
        $this->info("   📈 Centros de custo novos: {$this->stats['centros_custo_novos']}");
        $this->info("   ↻ Centros de custo atualizados: {$this->stats['centros_custo_atualizados']}");
        $this->info("   🗑️  Registros removidos: {$this->stats['removidos']}");
        $this->info("   ❌ Erros: {$this->stats['erros']}");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
