<?php

namespace App\Livewire;

use App\Services\OmieService;
use App\Models\ClienteFornecedor;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncProgress extends Component
{
    public $isRunning = false;
    public $progress = 0;
    public $currentStep = '';
    public $totalRecords = 0;
    public $processedRecords = 0;
    public $stats = [
        'clientes_criados' => 0,
        'clientes_atualizados' => 0,
        'fornecedores_criados' => 0,
        'fornecedores_atualizados' => 0,
        'centros_custo_criados' => 0,
        'centros_custo_atualizados' => 0,
        'erros' => 0
    ];
    public $completed = false;
    public $error = null;

    protected $listeners = ['startSync'];

    public function mount()
    {
        $this->loadProgress();
        
        // Se não há progresso salvo, reseta
        if (!$this->isRunning && !$this->completed && $this->progress === 0) {
            $this->resetProgress();
        }
    }

    public function startSync()
    {
        $this->isRunning = true;
        $this->progress = 0;
        $this->currentStep = 'Iniciando sincronização...';
        $this->error = null;
        $this->completed = false;
        $this->stats = [
            'clientes_criados' => 0,
            'clientes_atualizados' => 0,
            'fornecedores_criados' => 0,
            'fornecedores_atualizados' => 0,
            'centros_custo_criados' => 0,
            'centros_custo_atualizados' => 0,
            'erros' => 0
        ];

        try {
            // Gerar ID único para o job
            $jobId = uniqid('sync_', true);
            Log::info("Iniciando sincronização com jobId: {$jobId}");
            
            // Resetar progresso no cache
            $this->resetProgressCache($jobId);
            Log::info("Cache resetado para jobId: {$jobId}");
            
            // Disparar job em background
            \App\Jobs\SyncOmieDataJob::dispatch($jobId);
            Log::info("Job SyncOmieDataJob despachado para jobId: {$jobId}");
            
            // Armazenar o ID do job para acompanhar o progresso
            session(['sync_job_id' => $jobId]);
            Log::info("JobId armazenado na sessão: {$jobId}");
            
            $this->currentStep = 'Sincronização iniciada em background...';
            $this->updateProgressCache();
            
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->isRunning = false;
            $this->completed = false;
            $this->progress = 0;
            $this->currentStep = 'Erro ao iniciar sincronização';
            $this->updateProgressCache();
        }
    }

    /**
     * Atualiza o cache de progresso
     */
    private function updateProgressCache()
    {
        $jobId = session('sync_job_id');
        
        if (!$jobId) {
            return;
        }
        
        $progressData = [
            'percentage' => $this->progress,
            'current_step' => $this->currentStep,
            'processed_records' => $this->processedRecords,
            'total_records' => $this->totalRecords,
            'stats' => $this->stats,
            'completed' => $this->completed,
            'error' => $this->error,
            'updated_at' => now()->toISOString()
        ];

        Cache::put("sync_progress_{$jobId}", $progressData, 3600); // Cache por 1 hora
    }

    private function resetProgressCache($jobId)
    {
        $progressData = [
            'percentage' => 0,
            'current_step' => 'Preparando sincronização...',
            'processed_records' => 0,
            'total_records' => 0,
            'stats' => [
                'clientes_criados' => 0,
                'clientes_atualizados' => 0,
                'fornecedores_criados' => 0,
                'fornecedores_atualizados' => 0,
                'centros_custo_criados' => 0,
                'centros_custo_atualizados' => 0,
                'erros' => 0
            ],
            'completed' => false,
            'error' => null,
            'updated_at' => now()->toISOString()
        ];

        Cache::put("sync_progress_{$jobId}", $progressData, 3600); // Cache por 1 hora
    }

    /**
     * Carrega o progresso do cache
     */
    public function loadProgress()
    {
        $jobId = session('sync_job_id');
        
        if (!$jobId) {
            return;
        }
        
        $progressData = Cache::get("sync_progress_{$jobId}", []);
        
        if (!empty($progressData)) {
            $this->progress = $progressData['percentage'] ?? 0;
            $this->currentStep = $progressData['current_step'] ?? '';
            $this->processedRecords = $progressData['processed_records'] ?? 0;
            $this->totalRecords = $progressData['total_records'] ?? 0;
            
            // Garantir que stats sempre tenha a estrutura correta
            $defaultStats = [
                'clientes_criados' => 0,
                'clientes_atualizados' => 0,
                'fornecedores_criados' => 0,
                'fornecedores_atualizados' => 0,
                'centros_custo_criados' => 0,
                'centros_custo_atualizados' => 0,
                'erros' => 0
            ];
            $this->stats = array_merge($defaultStats, $progressData['stats'] ?? []);
            
            $this->completed = $progressData['completed'] ?? false;
            $this->error = $progressData['error'] ?? null;
            $this->isRunning = !$this->completed && !$this->error;
            
            // Se a sincronização foi concluída, disparar evento
            if ($this->completed && !$this->error) {
                $this->dispatch('sync-completed', [
                    'message' => $this->formatSyncMessage(),
                    'stats' => $this->stats
                ]);
                
                // Limpar o job ID da sessão
                session()->forget('sync_job_id');
            }
        }
    }

    private function syncData()
    {
        $omieService = app(OmieService::class);
        
        // Teste de conexão
        $this->currentStep = 'Testando conexão com API Omie...';
        $this->dispatch('progress-updated');
        
        $connectionTest = $omieService->testConnection();
        if (!$connectionTest['success']) {
            throw new \Exception('Falha na conexão: ' . $connectionTest['message']);
        }

        // Obter total de registros primeiro
        $this->currentStep = 'Obtendo informações dos registros...';
        $this->dispatch('progress-updated');
        
        $clientsResponse = $omieService->listClients(1, 1);
        $suppliersResponse = $omieService->listSuppliers(1, 1);
        $costCentersResponse = $omieService->listDepartments(1, 1);
        
        $totalClients = $clientsResponse['pagination']['total'] ?? 0;
        $totalSuppliers = $suppliersResponse['pagination']['total'] ?? 0;
        $totalCostCenters = $costCentersResponse['pagination']['total'] ?? 0;
        $this->totalRecords = $totalClients + $totalSuppliers + $totalCostCenters;

        // Sincronizar clientes
        $this->syncClients($omieService);
        
        // Sincronizar fornecedores
        $this->syncSuppliers($omieService);
        
        // Sincronizar centros de custo
        $this->syncCostCenters($omieService);
        
        $this->completed = true;
        $this->isRunning = false;
        $this->currentStep = 'Sincronização concluída!';
        $this->progress = 100;
        $this->dispatch('progress-updated');
        $this->dispatch('sync-completed', $this->stats);
    }

    private function syncClients($omieService)
    {
        $this->currentStep = 'Sincronizando clientes...';
        $this->dispatch('progress-updated');
        
        $page = 1;
        $perPage = 50;
        
        do {
            $response = $omieService->listClients($page, $perPage);
            
            if (empty($response['data'])) {
                break;
            }
            
            $clients = $response['data'];
            
            foreach ($clients as $clientData) {
                try {
                    $this->processClient($clientData);
                    $this->processedRecords++;
                    $this->updateProgress();
                } catch (\Exception $e) {
                    $this->stats['erros']++;
                    Log::warning('Erro ao processar cliente: ' . $e->getMessage());
                }
            }
            
            $page++;
            
        } while (count($clients) === $perPage);
    }

    private function syncSuppliers($omieService)
    {
        $this->currentStep = 'Sincronizando fornecedores...';
        $this->dispatch('progress-updated');
        
        $page = 1;
        $perPage = 50;
        
        do {
            $response = $omieService->listSuppliers($page, $perPage);
            
            if (empty($response['data'])) {
                break;
            }
            
            $suppliers = $response['data'];
            
            foreach ($suppliers as $supplierData) {
                try {
                    $this->processSupplier($supplierData);
                    $this->processedRecords++;
                    $this->updateProgress();
                } catch (\Exception $e) {
                    $this->stats['erros']++;
                    Log::warning('Erro ao processar fornecedor: ' . $e->getMessage());
                }
            }
            
            $page++;
            
        } while (count($suppliers) === $perPage);
    }

    private function processClient($clientData)
    {
        $existing = ClienteFornecedor::where('codigo_cliente_omie', $clientData['codigo'])
            ->where('is_cliente', true)
            ->first();

        if ($existing) {
            $existing->update([
                'razao_social' => $clientData['razao_social'],
                'nome_fantasia' => $clientData['nome_fantasia'],
                'cnpj_cpf' => $clientData['cnpj_cpf'],
                'email' => $clientData['email'],
                'telefone1_ddd' => $clientData['telefone1_ddd'],
                'telefone1_numero' => $clientData['telefone1_numero'],
                'endereco' => $clientData['endereco'],
                'endereco_numero' => $clientData['endereco_numero'],
                'bairro' => $clientData['bairro'],
                'cidade' => $clientData['cidade'],
                'estado' => $clientData['estado'],
                'cep' => $clientData['cep'],
                'status_sincronizacao' => 'sincronizado',
                'ultima_sincronizacao' => now(),
                'dados_originais_api' => $clientData['dados_originais'] ?? null,
            ]);
            $this->stats['clientes_atualizados']++;
        } else {
            ClienteFornecedor::create([
                'codigo_cliente_omie' => $clientData['codigo'],
                'codigo_cliente_integracao' => $clientData['codigo_integracao'],
                'razao_social' => $clientData['razao_social'],
                'nome_fantasia' => $clientData['nome_fantasia'],
                'cnpj_cpf' => $clientData['cnpj_cpf'],
                'email' => $clientData['email'],
                'telefone1_ddd' => $clientData['telefone1_ddd'],
                'telefone1_numero' => $clientData['telefone1_numero'],
                'endereco' => $clientData['endereco'],
                'endereco_numero' => $clientData['endereco_numero'],
                'bairro' => $clientData['bairro'],
                'cidade' => $clientData['cidade'],
                'estado' => $clientData['estado'],
                'cep' => $clientData['cep'],
                'is_cliente' => true,
                'importado_api' => 'S',
                'status_sincronizacao' => 'sincronizado',
                'ultima_sincronizacao' => now(),
                'dados_originais_api' => $clientData['dados_originais'] ?? null,
            ]);
            $this->stats['clientes_criados']++;
        }
    }

    private function processSupplier($supplierData)
    {
        $existing = ClienteFornecedor::where('codigo_cliente_omie', $supplierData['codigo'])
            ->where('is_cliente', false)
            ->first();

        if ($existing) {
            $existing->update([
                'razao_social' => $supplierData['razao_social'],
                'nome_fantasia' => $supplierData['nome_fantasia'],
                'cnpj_cpf' => $supplierData['cnpj_cpf'],
                'email' => $supplierData['email'],
                'telefone1_ddd' => $supplierData['telefone1_ddd'],
                'telefone1_numero' => $supplierData['telefone1_numero'],
                'endereco' => $supplierData['endereco'],
                'endereco_numero' => $supplierData['endereco_numero'],
                'bairro' => $supplierData['bairro'],
                'cidade' => $supplierData['cidade'],
                'estado' => $supplierData['estado'],
                'cep' => $supplierData['cep'],
                'status_sincronizacao' => 'sincronizado',
                'ultima_sincronizacao' => now(),
                'dados_originais_api' => $supplierData['dados_originais'] ?? null,
            ]);
            $this->stats['fornecedores_atualizados']++;
        } else {
            ClienteFornecedor::create([
                'codigo_cliente_omie' => $supplierData['codigo'],
                'codigo_cliente_integracao' => $supplierData['codigo_integracao'],
                'razao_social' => $supplierData['razao_social'],
                'nome_fantasia' => $supplierData['nome_fantasia'],
                'cnpj_cpf' => $supplierData['cnpj_cpf'],
                'email' => $supplierData['email'],
                'telefone1_ddd' => $supplierData['telefone1_ddd'],
                'telefone1_numero' => $supplierData['telefone1_numero'],
                'endereco' => $supplierData['endereco'],
                'endereco_numero' => $supplierData['endereco_numero'],
                'bairro' => $supplierData['bairro'],
                'cidade' => $supplierData['cidade'],
                'estado' => $supplierData['estado'],
                'cep' => $supplierData['cep'],
                'is_cliente' => false,
                'importado_api' => 'S',
                'status_sincronizacao' => 'sincronizado',
                'ultima_sincronizacao' => now(),
                'dados_originais_api' => $supplierData['dados_originais'] ?? null,
            ]);
            $this->stats['fornecedores_criados']++;
        }
    }

    private function syncCostCenters($omieService)
    {
        $this->currentStep = 'Sincronizando centros de custo...';
        $this->dispatch('progress-updated');
        
        $page = 1;
        $perPage = 50;
        
        do {
            $response = $omieService->listDepartments($page, $perPage);
            
            if (empty($response['data'])) {
                break;
            }
            
            $costCenters = $response['data'];
            
            foreach ($costCenters as $costCenterData) {
                try {
                    $this->processCostCenter($costCenterData);
                    $this->processedRecords++;
                    $this->updateProgress();
                } catch (\Exception $e) {
                    $this->stats['erros']++;
                    Log::warning('Erro ao processar centro de custo: ' . $e->getMessage());
                }
            }
            
            $page++;
            
        } while (count($costCenters) === $perPage);
    }

    private function processCostCenter($costCenterData)
    {
        $existing = \App\Models\CentroCusto::where('codigo_departamento_omie', $costCenterData['codigo_departamento_omie'])
            ->first();

        if ($existing) {
            $existing->update([
                'nome' => $costCenterData['nome'],
                'descricao' => $costCenterData['descricao'],
                'inativo' => $costCenterData['inativo'],
                'status_sincronizacao' => 'sincronizado',
                'ultima_sincronizacao' => now(),
                'dados_originais_api' => $costCenterData['dados_originais'] ?? null,
            ]);
            $this->stats['centros_custo_atualizados'] = ($this->stats['centros_custo_atualizados'] ?? 0) + 1;
        } else {
            \App\Models\CentroCusto::create([
                'codigo_departamento_omie' => $costCenterData['codigo_departamento_omie'],
                'codigo_departamento_integracao' => $costCenterData['codigo_departamento_integracao'],
                'nome' => $costCenterData['nome'],
                'descricao' => $costCenterData['descricao'],
                'inativo' => $costCenterData['inativo'],
                'importado_api' => 'S',
                'status_sincronizacao' => 'sincronizado',
                'ultima_sincronizacao' => now(),
                'dados_originais_api' => $costCenterData['dados_originais'] ?? null,
            ]);
            $this->stats['centros_custo_criados'] = ($this->stats['centros_custo_criados'] ?? 0) + 1;
        }
    }

    private function updateProgress()
    {
        if ($this->totalRecords > 0) {
            $this->progress = min(100, round(($this->processedRecords / $this->totalRecords) * 100));
        }
        $this->updateProgressCache();
        $this->dispatch('progress-updated');
    }

    private function resetProgress()
    {
        $this->isRunning = false;
        $this->progress = 0;
        $this->currentStep = '';
        $this->totalRecords = 0;
        $this->processedRecords = 0;
        $this->stats = [
            'clientes_criados' => 0,
            'clientes_atualizados' => 0,
            'fornecedores_criados' => 0,
            'fornecedores_atualizados' => 0,
            'centros_custo_criados' => 0,
            'centros_custo_atualizados' => 0,
            'erros' => 0
        ];
        $this->completed = false;
        $this->error = null;
    }

    private function formatSyncMessage()
    {
        if (empty($this->stats)) {
            return 'Sincronização concluída!';
        }

        $totalProcessados = $this->stats['total_processados'] ?? 0;
        $totalCriados = $this->stats['total_criados'] ?? 0;
        $totalAtualizados = $this->stats['total_atualizados'] ?? 0;
        $totalErros = $this->stats['total_erros'] ?? 0;

        $message = "Sincronização concluída!\n\n";
        $message .= "📊 Resumo:\n";
        $message .= "• Total processados: {$totalProcessados}\n";
        $message .= "• Novos registros: {$totalCriados}\n";
        $message .= "• Registros atualizados: {$totalAtualizados}\n";
        
        if ($totalErros > 0) {
            $message .= "• Erros: {$totalErros}\n";
        }

        // Detalhes por tipo
        if (isset($this->stats['clientes_processados']) && $this->stats['clientes_processados'] > 0) {
            $message .= "\n👥 Clientes:\n";
            $message .= "• Processados: {$this->stats['clientes_processados']}\n";
            $message .= "• Criados: {$this->stats['clientes_criados']}\n";
            $message .= "• Atualizados: {$this->stats['clientes_atualizados']}\n";
        }

        if (isset($this->stats['fornecedores_processados']) && $this->stats['fornecedores_processados'] > 0) {
            $message .= "\n🏢 Fornecedores:\n";
            $message .= "• Processados: {$this->stats['fornecedores_processados']}\n";
            $message .= "• Criados: {$this->stats['fornecedores_criados']}\n";
            $message .= "• Atualizados: {$this->stats['fornecedores_atualizados']}\n";
        }

        if (isset($this->stats['centros_custo_processados']) && $this->stats['centros_custo_processados'] > 0) {
            $message .= "\n🏷️ Centros de Custo:\n";
            $message .= "• Processados: {$this->stats['centros_custo_processados']}\n";
            $message .= "• Criados: {$this->stats['centros_custo_criados']}\n";
            $message .= "• Atualizados: {$this->stats['centros_custo_atualizados']}\n";
        }

        return $message;
    }

    public function render()
    {
        return view('livewire.sync-progress');
    }
}
