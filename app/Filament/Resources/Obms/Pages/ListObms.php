<?php

namespace App\Filament\Resources\Obms\Pages;

use App\Filament\Resources\Obms\ObmResource;
use App\Models\Obm;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListObms extends ListRecords
{
    protected static string $resource = ObmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos')
                ->icon('heroicon-m-list-bullet')
                ->badge(Obm::count()),
            
            'prestador' => Tab::make('Prestador')
                ->icon('heroicon-m-user-group')
                ->badge(Obm::whereHas('orcamento', fn (Builder $query) => 
                    $query->where('tipo_orcamento', 'prestador')
                )->count())
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereHas('orcamento', fn (Builder $subQuery) => 
                        $subQuery->where('tipo_orcamento', 'prestador')
                    )
                )
                ->badgeColor('info'),
            
            'aumento_km' => Tab::make('Aumento KM')
                ->icon('heroicon-m-arrow-trending-up')
                ->badge(Obm::whereHas('orcamento', fn (Builder $query) => 
                    $query->where('tipo_orcamento', 'aumento_km')
                )->count())
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereHas('orcamento', fn (Builder $subQuery) => 
                        $subQuery->where('tipo_orcamento', 'aumento_km')
                    )
                )
                ->badgeColor('warning'),
            
            'proprio_nova_rota' => Tab::make('Próprio Nova Rota')
                ->icon('heroicon-m-truck')
                ->badge(Obm::whereHas('orcamento', fn (Builder $query) => 
                    $query->where('tipo_orcamento', 'proprio_nova_rota')
                )->count())
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereHas('orcamento', fn (Builder $subQuery) => 
                        $subQuery->where('tipo_orcamento', 'proprio_nova_rota')
                    )
                )
                ->badgeColor('success'),
        ];
    }
}
