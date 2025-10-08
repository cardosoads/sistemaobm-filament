<?php

namespace App\Filament\Resources\ClienteFornecedors\Pages;

use App\Filament\Resources\ClienteFornecedors\ClienteFornecedorResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;

class ListClienteFornecedors extends ListRecords
{
    protected static string $resource = ClienteFornecedorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('syncOmie')
                ->label('Sincronizar com Omie')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Sincronizar Clientes e Fornecedores')
                ->modalDescription('Esta ação irá sincronizar todos os clientes e fornecedores com a API Omie. O processo pode levar alguns minutos.')
                ->modalSubmitActionLabel('Sincronizar')
                ->action(function () {
                    try {
                        // Executa o comando de sincronização
                        Artisan::call('omie:sync', [
                            '--type' => 'all',
                            '--force' => true
                        ]);
                        
                        $output = Artisan::output();
                        $stats = $this->extractSyncStats($output);
                        $message = $this->formatSyncMessage($stats);
                        
                        Notification::make()
                            ->title('Sincronização concluída!')
                            ->body($message)
                            ->success()
                            ->send();
                            
                        // Recarrega a página para mostrar os dados atualizados
                        $this->redirect(request()->header('Referer'));
                        
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erro na sincronização')
                            ->body('Ocorreu um erro durante a sincronização: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    /**
     * Extrai estatísticas da saída do comando de sincronização
     */
    private function extractSyncStats(string $output): array
    {
        $stats = [
            'clientes_novos' => 0,
            'clientes_atualizados' => 0,
            'fornecedores_novos' => 0,
            'fornecedores_atualizados' => 0,
            'removidos' => 0,
            'erros' => 0
        ];

        // Extrai números da saída usando regex
        if (preg_match('/Clientes novos: (\d+)/', $output, $matches)) {
            $stats['clientes_novos'] = (int) $matches[1];
        }
        if (preg_match('/Clientes atualizados: (\d+)/', $output, $matches)) {
            $stats['clientes_atualizados'] = (int) $matches[1];
        }
        if (preg_match('/Fornecedores novos: (\d+)/', $output, $matches)) {
            $stats['fornecedores_novos'] = (int) $matches[1];
        }
        if (preg_match('/Fornecedores atualizados: (\d+)/', $output, $matches)) {
            $stats['fornecedores_atualizados'] = (int) $matches[1];
        }
        if (preg_match('/Registros removidos: (\d+)/', $output, $matches)) {
            $stats['removidos'] = (int) $matches[1];
        }
        if (preg_match('/Erros: (\d+)/', $output, $matches)) {
            $stats['erros'] = (int) $matches[1];
        }

        return $stats;
    }

    /**
     * Formata mensagem de resultado da sincronização
     */
    private function formatSyncMessage(array $stats): string
    {
        $messages = [];
        
        if ($stats['clientes_novos'] > 0) {
            $messages[] = "{$stats['clientes_novos']} clientes criados";
        }
        
        if ($stats['clientes_atualizados'] > 0) {
            $messages[] = "{$stats['clientes_atualizados']} clientes atualizados";
        }
        
        if ($stats['fornecedores_novos'] > 0) {
            $messages[] = "{$stats['fornecedores_novos']} fornecedores criados";
        }
        
        if ($stats['fornecedores_atualizados'] > 0) {
            $messages[] = "{$stats['fornecedores_atualizados']} fornecedores atualizados";
        }
        
        if ($stats['removidos'] > 0) {
            $messages[] = "{$stats['removidos']} registros removidos";
        }
        
        if ($stats['erros'] > 0) {
            $messages[] = "{$stats['erros']} erros encontrados";
        }
        
        if (empty($messages)) {
            return 'Nenhuma alteração necessária. Todos os dados já estão sincronizados.';
        }
        
        return implode(', ', $messages) . '.';
    }
}
