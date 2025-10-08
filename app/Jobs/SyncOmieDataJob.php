<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\ClienteFornecedorSyncService;
use App\Services\CentroCustoSyncService;

class SyncOmieDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutos
    public $tries = 1;

    private string $jobId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $jobId)
    {
        $this->jobId = $jobId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("SyncOmieDataJob iniciado para jobId: {$this->jobId}");
            $this->updateProgress(0, 'Iniciando sincronização...', 0, 0);

            Log::info("Criando instâncias dos serviços de sincronização");
            $clienteFornecedorSyncService = app(ClienteFornecedorSyncService::class);
            $centroCustoSyncService = app(CentroCustoSyncService::class);
            
            // Executar sincronização completa
            Log::info("Iniciando sincronização completa");
            $this->updateProgress(10, 'Sincronizando clientes e fornecedores...', 0, 0);
            
            Log::info("Chamando syncTodos() para clientes e fornecedores");
            $resultadoClientesFornecedores = $clienteFornecedorSyncService->syncTodos();
            Log::info("syncTodos() de clientes e fornecedores concluído", ['resultado' => $resultadoClientesFornecedores]);
            
            $this->updateProgress(50, 'Sincronizando centros de custo...', 0, 0);
            
            Log::info("Chamando syncTodos() para centros de custo");
            $resultadoCentrosCusto = $centroCustoSyncService->syncTodos();
            Log::info("syncTodos() de centros de custo concluído", ['resultado' => $resultadoCentrosCusto]);
            
            if ($resultadoClientesFornecedores['sucesso'] && $resultadoCentrosCusto['sucesso']) {
                $totalProcessados = $resultadoClientesFornecedores['clientes']['processados'] + 
                                  $resultadoClientesFornecedores['fornecedores']['processados'] +
                                  $resultadoCentrosCusto['centros_custo']['processados'];
                                  
                $totalCriados = $resultadoClientesFornecedores['clientes']['criados'] + 
                              $resultadoClientesFornecedores['fornecedores']['criados'] +
                              $resultadoCentrosCusto['centros_custo']['criados'];
                              
                $totalAtualizados = $resultadoClientesFornecedores['clientes']['atualizados'] + 
                                  $resultadoClientesFornecedores['fornecedores']['atualizados'] +
                                  $resultadoCentrosCusto['centros_custo']['atualizados'];
                                  
                $totalErros = count($resultadoClientesFornecedores['clientes']['erros']) + 
                            count($resultadoClientesFornecedores['fornecedores']['erros']) +
                            count($resultadoCentrosCusto['centros_custo']['erros']);

                $stats = [
                    'clientes_processados' => $resultadoClientesFornecedores['clientes']['processados'],
                    'clientes_criados' => $resultadoClientesFornecedores['clientes']['criados'],
                    'clientes_atualizados' => $resultadoClientesFornecedores['clientes']['atualizados'],
                    'fornecedores_processados' => $resultadoClientesFornecedores['fornecedores']['processados'],
                    'fornecedores_criados' => $resultadoClientesFornecedores['fornecedores']['criados'],
                    'fornecedores_atualizados' => $resultadoClientesFornecedores['fornecedores']['atualizados'],
                    'centros_custo_processados' => $resultadoCentrosCusto['centros_custo']['processados'],
                    'centros_custo_criados' => $resultadoCentrosCusto['centros_custo']['criados'],
                    'centros_custo_atualizados' => $resultadoCentrosCusto['centros_custo']['atualizados'],
                    'total_processados' => $totalProcessados,
                    'total_criados' => $totalCriados,
                    'total_atualizados' => $totalAtualizados,
                    'total_erros' => $totalErros
                ];

                $this->updateProgress(
                    100, 
                    'Sincronização concluída com sucesso!',
                    $totalProcessados,
                    $totalProcessados,
                    $stats,
                    true
                );
            } else {
                $mensagemErro = '';
                if (!$resultadoClientesFornecedores['sucesso']) {
                    $mensagemErro .= 'Erro na sincronização de clientes/fornecedores: ' . $resultadoClientesFornecedores['mensagem'];
                }
                if (!$resultadoCentrosCusto['sucesso']) {
                    if ($mensagemErro) $mensagemErro .= ' | ';
                    $mensagemErro .= 'Erro na sincronização de centros de custo: ' . $resultadoCentrosCusto['mensagem'];
                }
                
                $this->updateProgress(
                    0, 
                    'Erro na sincronização',
                    0,
                    0,
                    [],
                    false,
                    $mensagemErro
                );
            }

        } catch (\Exception $e) {
            Log::error("Erro no SyncOmieDataJob", [
                'jobId' => $this->jobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->updateProgress(
                0, 
                'Erro na sincronização: ' . $e->getMessage(), 
                0, 
                0, 
                [], 
                false, 
                $e->getMessage()
            );
        }
    }

    /**
     * Atualiza o progresso da sincronização
     */
    private function updateProgress(
        int $percentage, 
        string $currentStep, 
        int $processedRecords, 
        int $totalRecords, 
        array $stats = [],
        bool $completed = false,
        string $error = null
    ): void {
        $progressData = [
            'percentage' => $percentage,
            'current_step' => $currentStep,
            'processed_records' => $processedRecords,
            'total_records' => $totalRecords,
            'stats' => $stats,
            'completed' => $completed,
            'error' => $error,
            'updated_at' => now()->toISOString()
        ];

        Log::info("Atualizando progresso", [
            'jobId' => $this->jobId,
            'percentage' => $percentage,
            'currentStep' => $currentStep,
            'cacheKey' => "sync_progress_{$this->jobId}"
        ]);

        Cache::put("sync_progress_{$this->jobId}", $progressData, 3600); // Cache por 1 hora
    }
}
