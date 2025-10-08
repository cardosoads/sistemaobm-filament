<?php

namespace App\Filament\Widgets;

use App\Models\CentroCusto;
use App\Models\Base;
use App\Models\RecursoHumano;
use App\Models\Obm;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperacoesWidget extends BaseWidget
{
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $centrosCusto = CentroCusto::count();
        $basesOperacionais = Base::count();
        $recursosHumanos = RecursoHumano::where('active', true)->count();
        $obmsAtivas = Obm::whereNotNull('data_inicio')
            ->where(function($query) {
                $query->whereNull('data_fim')
                    ->orWhere('data_fim', '>', now());
            })
            ->count();

        return [
            Stat::make('Centros de Custo', $centrosCusto)
                ->description('Centros cadastrados')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make('Bases Operacionais', $basesOperacionais)
                ->description('Bases de operação')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('info'),

            Stat::make('Recursos Humanos', $recursosHumanos)
                ->description('Cargos ativos')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('success'),

            Stat::make('OBMs Ativas', $obmsAtivas)
                ->description('Operações em andamento')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('warning'),
        ];
    }
}