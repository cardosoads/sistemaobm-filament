<?php

namespace App\Console\Commands;

use App\Models\Orcamento;
use App\Models\OrcamentoPrestador;
use App\Models\OrcamentoAumentoKm;
use App\Models\OrcamentoProprioNovaRota;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LimparOrcamentos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orcamentos:limpar {--force : Força a limpeza sem confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpa todos os orçamentos e dados relacionados do banco de dados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Verificar quantos registros existem
        $orcamentosCount = Orcamento::count();
        $prestadoresCount = OrcamentoPrestador::count();
        $aumentoKmCount = OrcamentoAumentoKm::count();
        $proprioNovaRotaCount = OrcamentoProprioNovaRota::count();

        $this->info("Registros encontrados:");
        $this->line("- Orçamentos: {$orcamentosCount}");
        $this->line("- Prestadores: {$prestadoresCount}");
        $this->line("- Aumento KM: {$aumentoKmCount}");
        $this->line("- Próprio Nova Rota: {$proprioNovaRotaCount}");
        $this->line("");

        if ($orcamentosCount === 0) {
            $this->info('Não há orçamentos para limpar.');
            return 0;
        }

        // Confirmação de segurança
        if (!$this->option('force')) {
            if (!$this->confirm('Tem certeza que deseja limpar TODOS os orçamentos? Esta ação não pode ser desfeita.')) {
                $this->info('Operação cancelada.');
                return 0;
            }

            if (!$this->confirm('ATENÇÃO: Isso irá deletar permanentemente todos os dados de orçamentos. Confirma novamente?')) {
                $this->info('Operação cancelada.');
                return 0;
            }
        }

        $this->info('Iniciando limpeza dos orçamentos...');

        try {
            DB::transaction(function () {
                // Deletar em ordem para respeitar as foreign keys
                $this->info('Deletando registros de Prestadores...');
                $prestadoresDeletados = OrcamentoPrestador::count();
                OrcamentoPrestador::query()->delete();
                $this->line("  → {$prestadoresDeletados} registros deletados");
                
                $this->info('Deletando registros de Aumento KM...');
                $aumentoKmDeletados = OrcamentoAumentoKm::count();
                OrcamentoAumentoKm::query()->delete();
                $this->line("  → {$aumentoKmDeletados} registros deletados");
                
                $this->info('Deletando registros de Próprio Nova Rota...');
                $proprioNovaRotaDeletados = OrcamentoProprioNovaRota::count();
                OrcamentoProprioNovaRota::query()->delete();
                $this->line("  → {$proprioNovaRotaDeletados} registros deletados");
                
                $this->info('Deletando orçamentos principais...');
                $orcamentosDeletados = Orcamento::count();
                Orcamento::query()->delete();
                $this->line("  → {$orcamentosDeletados} registros deletados");
            });

            $this->info('✅ Limpeza concluída com sucesso!');
            
            // Verificar se realmente foi limpo
            $this->info('Verificando limpeza...');
            $this->line("- Orçamentos restantes: " . Orcamento::count());
            $this->line("- Prestadores restantes: " . OrcamentoPrestador::count());
            $this->line("- Aumento KM restantes: " . OrcamentoAumentoKm::count());
            $this->line("- Próprio Nova Rota restantes: " . OrcamentoProprioNovaRota::count());

        } catch (\Exception $e) {
            $this->error('Erro durante a limpeza: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
