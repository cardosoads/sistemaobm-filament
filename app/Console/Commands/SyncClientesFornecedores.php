<?php

namespace App\Console\Commands;

use App\Services\ClienteFornecedorSyncService;
use Illuminate\Console\Command;

class SyncClientesFornecedores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:clientes-fornecedores 
                            {--tipo=todos : Tipo de sincronização (clientes, fornecedores, todos)}
                            {--pagina=1 : Página inicial para sincronização}
                            {--registros=50 : Registros por página}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza clientes e fornecedores com a API Omie';

    protected $syncService;

    public function __construct(ClienteFornecedorSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tipo = $this->option('tipo');
        $pagina = (int) $this->option('pagina');
        $registros = (int) $this->option('registros');

        $this->info("🚀 Iniciando sincronização de {$tipo}...");
        $this->newLine();

        try {
            switch ($tipo) {
                case 'clientes':
                    $this->syncClientes($pagina, $registros);
                    break;
                
                case 'fornecedores':
                    $this->syncFornecedores($pagina, $registros);
                    break;
                
                case 'todos':
                default:
                    $this->syncTodos();
                    break;
            }

            $this->newLine();
            $this->info('✅ Sincronização concluída com sucesso!');

        } catch (\Exception $e) {
            $this->error('❌ Erro durante a sincronização: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function syncClientes(int $pagina, int $registros): void
    {
        $this->info("📥 Sincronizando clientes (página {$pagina}, {$registros} registros por página)...");
        
        $progressBar = null;
        $paginaAtual = $pagina;

        do {
            $resultado = $this->syncService->syncClientes($paginaAtual, $registros);

            if (!$resultado['sucesso']) {
                throw new \Exception($resultado['erro']);
            }

            // Criar barra de progresso na primeira iteração
            if ($progressBar === null && isset($resultado['total_registros'])) {
                $progressBar = $this->output->createProgressBar($resultado['total_registros']);
                $progressBar->setFormat('verbose');
            }

            // Atualizar barra de progresso
            if ($progressBar) {
                $progressBar->advance($resultado['processados']);
            }

            $this->line("Página {$paginaAtual}: {$resultado['processados']} processados, {$resultado['criados']} criados, {$resultado['atualizados']} atualizados");

            if (!empty($resultado['erros'])) {
                $this->warn("⚠️  {$resultado['erros']} erros encontrados na página {$paginaAtual}");
                foreach ($resultado['erros'] as $erro) {
                    $this->line("  - Código {$erro['codigo_omie']}: {$erro['erro']}");
                }
            }

            $paginaAtual++;
        } while ($paginaAtual <= ($resultado['total_paginas'] ?? 1));

        if ($progressBar) {
            $progressBar->finish();
            $this->newLine();
        }
    }

    protected function syncFornecedores(int $pagina, int $registros): void
    {
        $this->info("📥 Sincronizando fornecedores (página {$pagina}, {$registros} registros por página)...");
        
        $progressBar = null;
        $paginaAtual = $pagina;

        do {
            $resultado = $this->syncService->syncFornecedores($paginaAtual, $registros);

            if (!$resultado['sucesso']) {
                throw new \Exception($resultado['erro']);
            }

            // Criar barra de progresso na primeira iteração
            if ($progressBar === null && isset($resultado['total_registros'])) {
                $progressBar = $this->output->createProgressBar($resultado['total_registros']);
                $progressBar->setFormat('verbose');
            }

            // Atualizar barra de progresso
            if ($progressBar) {
                $progressBar->advance($resultado['processados']);
            }

            $this->line("Página {$paginaAtual}: {$resultado['processados']} processados, {$resultado['criados']} criados, {$resultado['atualizados']} atualizados");

            if (!empty($resultado['erros'])) {
                $this->warn("⚠️  {$resultado['erros']} erros encontrados na página {$paginaAtual}");
                foreach ($resultado['erros'] as $erro) {
                    $this->line("  - Código {$erro['codigo_omie']}: {$erro['erro']}");
                }
            }

            $paginaAtual++;
        } while ($paginaAtual <= ($resultado['total_paginas'] ?? 1));

        if ($progressBar) {
            $progressBar->finish();
            $this->newLine();
        }
    }

    protected function syncTodos(): void
    {
        $this->info("📥 Sincronizando todos os clientes e fornecedores...");
        
        $resultado = $this->syncService->syncTodos();

        if (!$resultado['sucesso']) {
            throw new \Exception($resultado['mensagem']);
        }

        $this->table(
            ['Tipo', 'Processados', 'Criados', 'Atualizados', 'Erros'],
            [
                [
                    'Clientes',
                    $resultado['clientes']['processados'],
                    $resultado['clientes']['criados'],
                    $resultado['clientes']['atualizados'],
                    count($resultado['clientes']['erros'])
                ],
                [
                    'Fornecedores',
                    $resultado['fornecedores']['processados'],
                    $resultado['fornecedores']['criados'],
                    $resultado['fornecedores']['atualizados'],
                    count($resultado['fornecedores']['erros'])
                ]
            ]
        );

        // Mostrar erros se houver
        $todosErros = array_merge($resultado['clientes']['erros'], $resultado['fornecedores']['erros']);
        if (!empty($todosErros)) {
            $this->warn("⚠️  Erros encontrados durante a sincronização:");
            foreach ($todosErros as $erro) {
                $this->line("  - Código {$erro['codigo_omie']}: {$erro['erro']}");
            }
        }

        $this->info($resultado['mensagem']);
    }
}
