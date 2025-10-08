<?php

namespace App\Filament\Widgets;

use App\Models\ClienteFornecedor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClientesFornecedoresWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalClientes = ClienteFornecedor::where('is_cliente', true)->count();
        $totalFornecedores = ClienteFornecedor::where('is_cliente', false)->count();
        $clientesAtivos = ClienteFornecedor::where('is_cliente', true)
            ->where('inativo', false)
            ->count();
        $totalRegistros = ClienteFornecedor::count();

        return [
            Stat::make('Total de Clientes', $totalClientes)
                ->description('Clientes cadastrados')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Clientes Ativos', $clientesAtivos)
                ->description('Clientes em atividade')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Total de Fornecedores', $totalFornecedores)
                ->description('Fornecedores cadastrados')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            Stat::make('Total Geral', $totalRegistros)
                ->description('Clientes + Fornecedores')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}