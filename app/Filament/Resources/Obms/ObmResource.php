<?php

namespace App\Filament\Resources\Obms;

use App\Filament\Resources\Obms\Pages\CreateObm;
use App\Filament\Resources\Obms\Pages\EditObm;
use App\Filament\Resources\Obms\Pages\ListObms;
use App\Filament\Resources\Obms\Schemas\ObmForm;
use App\Filament\Resources\Obms\Tables\ObmsTable;
use App\Models\Obm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ObmResource extends Resource
{
    protected static ?string $model = Obm::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Document;

    public static function form(Schema $schema): Schema
    {
        return ObmForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ObmsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListObms::route('/'),
            'create' => CreateObm::route('/create'),
            'edit' => EditObm::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['orcamento', 'colaborador', 'frota', 'user']);

        $user = auth()->user();
        if ($user) {
            if ($user->hasAnyRole(['Recursos Humanos', 'RH'])) {
                // RH vê OBMs que deveriam ter funcionário MAS ainda não têm colaborador_id preenchido
                $query->whereNull('colaborador_id')
                      ->whereHas('orcamento', function (Builder $subQuery) {
                    $subQuery->where(function (Builder $innerQuery) {
                        // Tipos que não são prestador nem aumento_km sempre deveriam ter funcionário
                        $innerQuery->whereNotIn('tipo_orcamento', ['prestador', 'aumento_km'])
                                  // OU tipo proprio_nova_rota com incluir_funcionario = true na tabela orcamento_proprio_nova_rota
                                  ->orWhere(function (Builder $novaRotaQuery) {
                                      $novaRotaQuery->where('tipo_orcamento', 'proprio_nova_rota')
                                                   ->whereHas('propriosNovaRota', function (Builder $proprioQuery) {
                                                       $proprioQuery->where('incluir_funcionario', true);
                                                   });
                                  });
                    });
                });
            } elseif ($user->hasAnyRole(['Frotas'])) {
                 // Frotas vê OBMs que deveriam ter frota MAS ainda não têm frota_id preenchido
                 $query->whereNull('frota_id')
                       ->whereHas('orcamento', function (Builder $subQuery) {
                     // Apenas orçamentos do tipo proprio_nova_rota com incluir_frota = true precisam de frota
                     $subQuery->where('tipo_orcamento', 'proprio_nova_rota')
                              ->whereHas('propriosNovaRota', function (Builder $proprioQuery) {
                                  $proprioQuery->where('incluir_frota', true);
                              });
                 });
            } else {
                // Para outros usuários, aplicar filtro para não mostrar OBMs que só têm prestador
                $query->where(function (Builder $subQuery) {
                    $subQuery
                        // Mostrar se tem funcionário OU frota
                        ->where(function (Builder $innerQuery) {
                            $innerQuery->whereNotNull('colaborador_id')
                                      ->orWhereNotNull('frota_id');
                        })
                        // OU se não é do tipo prestador
                        ->orWhereHas('orcamento', function (Builder $orcQuery) {
                            $orcQuery->where('tipo_orcamento', '!=', 'prestador');
                        });
                });
            }
        }

        return $query;
    }

    public static function getGloballySearchableAttributes(): array
    {
        // Busca nos campos relacionados do orçamento
        return ['orcamento.nome_rota', 'orcamento.cliente_nome'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Orçamento' => $record->orcamento?->nome_rota,
            'Cliente' => $record->orcamento?->cliente_nome,
            'Origem' => $record->orcamento?->origem,
            'Destino' => $record->orcamento?->destino,
            'Colaborador' => $record->colaborador?->nome,
            'Veículo' => $record->frota?->tipoVeiculo?->nome,
            'Período' => $record->periodo,
            'Status' => $record->status_label,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole([
            'Administrador',
            'Gerente',
            'Fornecedor',
            'Recursos Humanos',
            'Frotas',
        ]);
    }
}
