<?php

namespace App\Filament\Resources\CentroCustos\Pages;

use App\Filament\Resources\CentroCustos\CentroCustoResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;

class ListCentroCustos extends ListRecords
{
    protected static string $resource = CentroCustoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('syncCentrosCusto')
                ->label('Sincronizar com Omie')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Sincronizar Centros de Custo')
                ->modalDescription('Esta ação irá sincronizar todos os centros de custo com a API Omie. O processo pode levar alguns minutos.')
                ->modalSubmitActionLabel('Sincronizar')
                ->action(function () {
                    try {
                        // Executa o comando de sincronização específico para centros de custo
                        Artisan::call('sync:centros-custo');
                        
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
            'centros_custo_novos' => 0,
            'centros_custo_atualizados' => 0,
            'erros' => 0
        ];

        // Extrai números da saída usando regex
        if (preg_match('/Criados: (\d+)/', $output, $matches)) {
            $stats['centros_custo_novos'] = (int) $matches[1];
        }
        if (preg_match('/Atualizados: (\d+)/', $output, $matches)) {
            $stats['centros_custo_atualizados'] = (int) $matches[1];
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
        
        if ($stats['centros_custo_novos'] > 0) {
            $messages[] = "{$stats['centros_custo_novos']} centros de custo criados";
        }
        
        if ($stats['centros_custo_atualizados'] > 0) {
            $messages[] = "{$stats['centros_custo_atualizados']} centros de custo atualizados";
        }
        
        if ($stats['erros'] > 0) {
            $messages[] = "{$stats['erros']} erros encontrados";
        }
        
        if (empty($messages)) {
            return 'Nenhuma alteração necessária. Todos os centros de custo já estão sincronizados.';
        }
        
        return implode(', ', $messages) . '.';
    }
}
