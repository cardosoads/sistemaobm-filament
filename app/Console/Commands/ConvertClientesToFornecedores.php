<?php

namespace App\Console\Commands;

use App\Models\ClienteFornecedor;
use Illuminate\Console\Command;

class ConvertClientesToFornecedores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientes:converter-para-fornecedores
                            {--dry-run : Apenas mostra quantos serão convertidos sem executar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converte todos os registros com is_cliente=true para is_cliente=false (fornecedor)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $totalClientes = ClienteFornecedor::where('is_cliente', true)->count();

        if ($totalClientes === 0) {
            $this->info('Nenhum registro com is_cliente=true encontrado. Nada a converter.');
            return Command::SUCCESS;
        }

        $this->info("Encontrados {$totalClientes} registros com is_cliente=true");

        if ($dryRun) {
            $this->warn('Modo dry-run: nenhuma alteração foi feita.');
            $this->info("Execute sem --dry-run para converter os {$totalClientes} registros.");
            return Command::SUCCESS;
        }

        if (!$this->confirm("Deseja converter {$totalClientes} registros de cliente para fornecedor?")) {
            $this->info('Operação cancelada.');
            return Command::SUCCESS;
        }

        $updated = ClienteFornecedor::where('is_cliente', true)->update(['is_cliente' => false]);

        $this->info("Convertidos {$updated} registros de cliente para fornecedor com sucesso!");

        return Command::SUCCESS;
    }
}
