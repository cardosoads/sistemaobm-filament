<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\OmieController;
use Illuminate\Http\Request;

class TestController extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa o controller OmieController diretamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testando o controller OmieController...');
        
        try {
            $controller = app(OmieController::class);
            
            // Criar uma request simulada
            $request = new Request();
            $request->merge([
                'page' => 1,
                'per_page' => 3
            ]);
            
            $this->info('Chamando listClients...');
            
            $response = $controller->listClients($request);
            
            $this->info('Status: ' . $response->getStatusCode());
            $this->info('Conteúdo: ' . $response->getContent());
            
        } catch (\Exception $e) {
            $this->error('❌ Exceção capturada: ' . $e->getMessage());
            $this->error('Arquivo: ' . $e->getFile() . ':' . $e->getLine());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}