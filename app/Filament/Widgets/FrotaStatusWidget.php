<?php

namespace App\Filament\Widgets;

use App\Models\Frota;
use App\Models\Veiculo;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FrotaStatusWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $frotasAtivas = Frota::where('active', true)->count();
        $frotasInativas = Frota::where('active', false)->count();
        $custoTotalFrota = Frota::where('active', true)->sum('custo_total') ?? 0;
        $totalVeiculos = Veiculo::count();

        return [
            Stat::make('Frotas Ativas', $frotasAtivas)
                ->description('Frotas em operação')
                ->descriptionIcon('heroicon-m-truck')
                ->color('success'),

            Stat::make('Frotas Inativas', $frotasInativas)
                ->description('Frotas fora de operação')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Custo Total Ativo', 'R$ ' . number_format($custoTotalFrota, 2, ',', '.'))
                ->description('Custo das frotas ativas')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),

            Stat::make('Total de Veículos', $totalVeiculos)
                ->description('Veículos cadastrados')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),
        ];
    }
}