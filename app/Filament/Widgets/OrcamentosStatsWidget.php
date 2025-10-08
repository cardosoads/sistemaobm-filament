<?php

namespace App\Filament\Widgets;

use App\Models\Orcamento;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrcamentosStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $totalOrcamentos = Orcamento::count();
        $orcamentosPendentes = Orcamento::where('status', 'pendente')->count();
        $orcamentosAprovados = Orcamento::where('status', 'aprovado')->count();
        $valorTotalOrcamentos = Orcamento::sum('valor_total') ?? 0;

        return [
            Stat::make('Total de Orçamentos', $totalOrcamentos)
                ->description('Todos os orçamentos no sistema')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Orçamentos Pendentes', $orcamentosPendentes)
                ->description('Aguardando aprovação')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Orçamentos Aprovados', $orcamentosAprovados)
                ->description('Aprovados e em andamento')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Valor Total', 'R$ ' . number_format($valorTotalOrcamentos, 2, ',', '.'))
                ->description('Soma de todos os orçamentos')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),
        ];
    }
}