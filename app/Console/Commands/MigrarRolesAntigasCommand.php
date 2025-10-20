<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class MigrarRolesAntigasCommand extends Command
{
    protected $signature = 'roles:migrar-antigas {--dry-run : Apenas exibe o que será feito, sem aplicar mudanças}';
    protected $description = 'Migra usuários das roles antigas (admin, gestor, operador) para as novas e remove as antigas';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');

        $mapeamento = [
            'admin' => 'Administrador',
            'gestor' => 'Gerente',
            'operador' => 'Orçamento',
        ];

        $this->info('Iniciando migração de roles antigas...');

        DB::beginTransaction();
        try {
            foreach ($mapeamento as $antiga => $nova) {
                $roleAntiga = Role::where('name', $antiga)->first();
                $roleNova = Role::where('name', $nova)->first();

                if (! $roleAntiga) {
                    $this->warn("Role antiga '{$antiga}' não encontrada. Pulando.");
                    continue;
                }

                if (! $roleNova) {
                    $this->warn("Role nova '{$nova}' não encontrada. Criando...");
                    if (! $dryRun) {
                        $roleNova = Role::create(['name' => $nova]);
                    }
                }

                $usuarios = $roleAntiga->users; // relação Spatie
                $count = $usuarios->count();
                $this->info("Migrando {$count} usuário(s) de '{$antiga}' para '{$nova}'...");

                foreach ($usuarios as $usuario) {
                    if ($dryRun) {
                        $this->line(" - [DRY RUN] {$usuario->email} receberá role '{$nova}' e será removida '{$antiga}'");
                        continue;
                    }
                    $usuario->removeRole($roleAntiga);
                    $usuario->assignRole($roleNova);
                }

                if (! $dryRun) {
                    $roleAntiga->delete();
                    $this->info("Role antiga '{$antiga}' removida.");
                } else {
                    $this->line(" - [DRY RUN] Role antiga '{$antiga}' seria removida");
                }
            }

            if ($dryRun) {
                DB::rollBack();
                $this->info('DRY RUN concluído. Nenhuma alteração aplicada.');
            } else {
                DB::commit();
                $this->info('Migração concluída com sucesso!');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Erro durante a migração: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}