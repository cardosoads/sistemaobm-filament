<?php

namespace App\Console\Commands;

use App\Services\CentroCustoSyncService;
use Illuminate\Console\Command;

class SyncCentrosCusto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:centros-custo 
                            {--pagina=1 : Página inicial para sincronização}
                            {--registros=50 : Registros por página}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza centros de custo com a API Omie';

    protected $syncService;

    public function __construct(CentroCustoSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pagina = (int) $this->option('pagina');
        $registros = (int) $this->option('registros');

        $this->info("🚀 Iniciando sincronização de centros de custo...");
        $this->newLine();

        try {
            $this->syncCentrosCusto($pagina, $registros);

            $this->newLine();
            $this->info('✅ Sincronização de centros de custo concluída com sucesso!');

        } catch (\Exception $e) {
            $this->error('❌ Erro durante a sincronização: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Sincroniza todos os centros de custo
     */
    private function syncCentrosCusto(int $paginaInicial = 1, int $registrosPorPagina = 50): void
    {
        $this->info('📥 Sincronizando centros de custo...');
        
        $resultado = $this->syncService->syncTodos();
        
        if ($resultado['sucesso']) {
            $this->info("   ✅ Centros de custo processados:");
            $this->info("      • Processados: {$resultado['centros_custo']['processados']}");
            $this->info("      • Criados: {$resultado['centros_custo']['criados']}");
            $this->info("      • Atualizados: {$resultado['centros_custo']['atualizados']}");
            
            if (!empty($resultado['centros_custo']['erros'])) {
                $this->warn("      • Erros: " . count($resultado['centros_custo']['erros']));
                foreach ($resultado['centros_custo']['erros'] as $erro) {
                    $this->warn("        - Código {$erro['codigo_omie']}: {$erro['erro']}");
                }
            }
        } else {
            throw new \Exception($resultado['mensagem'] ?? 'Erro desconhecido na sincronização');
        }
    }
}