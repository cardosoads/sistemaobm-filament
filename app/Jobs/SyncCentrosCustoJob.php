<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\CentroCustoSyncService;
use Filament\Notifications\Notification;

class SyncCentrosCustoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutos
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
            Log::info("SyncCentrosCustoJob iniciado para jobId: {$this->jobId}");
            $this->updateProgress(0, 'Iniciando sincronização de centros de custo...', 0, 0);

            $centroCustoSyncService = app(CentroCustoSyncService::class);
            
            $this->updateProgress(10, 'Sincronizando centros de custo...', 0, 0);
            
            Log::info("Chamando syncTodos() para centros de custo");
            $resultado = $centroCustoSyncService->syncTodos();
            Log::info("syncTodos() de centros de custo concluído", ['resultado' => $resultado]);

            if ($resultado['sucesso']) {
                $totalProcessados = $resultado['centros_custo']['processados'];
                $totalCriados = $resultado['centros_custo']['criados'];
                $totalAtualizados = $resultado['centros_custo']['atualizados'];
                $totalErros = count($resultado['centros_custo']['erros']);

                $stats = [
                    'centros_custo_processados' => $totalProcessados,
                    'centros_custo_criados' => $totalCriados,
                    'centros_custo_atualizados' => $totalAtualizados,
                    'total_erros' => $totalErros
                ];

                $this->updateProgress(
                    100, 
                    'Sincronização de centros de custo concluída com sucesso!',
                    $totalProcessados,
                    $totalProcessados,
                    $stats,
                    true
                );
            } else {
                $this->updateProgress(
                    0, 
                    'Erro na sincronização de centros de custo',
                    0,
                    0,
                    [],
                    false,
                    $resultado['mensagem'] ?? 'Erro desconhecido'
                );
            }

        } catch (\Exception $e) {
            Log::error("Erro no SyncCentrosCustoJob", [
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

