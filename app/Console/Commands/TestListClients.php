<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OmieService;

class TestListClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:list-clients {--page=1} {--per_page=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a função listClients do OmieService';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testando a função listClients...');
        
        try {
            $omieService = app(OmieService::class);
            
            $page = $this->option('page');
            $perPage = $this->option('per_page');
            
            $this->info("Buscando clientes - Página: {$page}, Por página: {$perPage}");
            
            $result = $omieService->listClients($page, $perPage, []);
            
            if ($result['success']) {
                $this->info('✅ Sucesso na busca de clientes!');
                $this->info('Total de clientes encontrados: ' . count($result['data']));
                
                if (!empty($result['data'])) {
                    $this->info('Primeiros clientes:');
                    foreach (array_slice($result['data'], 0, 3) as $cliente) {
                        $this->line('- ' . ($cliente['razao_social'] ?? 'N/A') . ' (' . ($cliente['cnpj_cpf'] ?? 'N/A') . ')');
                    }
                }
                
                if (isset($result['pagination'])) {
                    $this->info('Paginação: ' . json_encode($result['pagination'], JSON_PRETTY_PRINT));
                }
            } else {
                $this->error('❌ Erro na busca de clientes: ' . ($result['message'] ?? 'Erro desconhecido'));
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Exceção capturada: ' . $e->getMessage());
            $this->error('Arquivo: ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}
