<?php

namespace App\Filament\Resources\CentroCustos\Pages;

use App\Filament\Resources\CentroCustos\CentroCustoResource;
use App\Jobs\SyncCentrosCustoJob;
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
                ->modalDescription('Esta ação irá sincronizar todos os centros de custo com a API Omie. O processo será executado em background e pode levar alguns minutos.')
                ->modalSubmitActionLabel('Sincronizar')
                ->action(function () {
                    try {
                        // Gerar ID único para o job
                        $jobId = uniqid('sync_centros_custo_', true);
                        
                        // Disparar job em background
                        SyncCentrosCustoJob::dispatch($jobId);
                        
                        Notification::make()
                            ->title('Sincronização iniciada!')
                            ->body('A sincronização de centros de custo foi iniciada em background. Você será notificado quando concluir.')
                            ->success()
                            ->send();
                            
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erro ao iniciar sincronização')
                            ->body('Ocorreu um erro ao iniciar a sincronização: ' . $e->getMessage())
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
