<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Frota extends Model
{
    protected $table = 'frotas';

    protected $fillable = [
        'tipo_veiculo_id',
        'fipe',
        'percentual_aluguel',
        'aluguel_carro',
        'rastreador',
        'percentual_provisoes_avarias',
        'provisoes_avarias',
        'percentual_provisao_desmobilizacao',
        'provisao_desmobilizacao',
        'percentual_provisao_rac',
        'provisao_diaria_rac',
        'custo_total',
        'active',
    ];

    protected $casts = [
        'fipe' => 'decimal:2',
        'percentual_aluguel' => 'decimal:2',
        'aluguel_carro' => 'decimal:2',
        'rastreador' => 'decimal:2',
        'percentual_provisoes_avarias' => 'decimal:2',
        'provisoes_avarias' => 'decimal:2',
        'percentual_provisao_desmobilizacao' => 'decimal:2',
        'provisao_desmobilizacao' => 'decimal:2',
        'percentual_provisao_rac' => 'decimal:2',
        'provisao_diaria_rac' => 'decimal:2',
        'custo_total' => 'decimal:2',
        'active' => 'boolean',
    ];

    // Relacionamentos
    public function tipoVeiculo(): BelongsTo
    {
        return $this->belongsTo(TipoVeiculo::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeByTipoVeiculo(Builder $query, int $tipoVeiculoId): Builder
    {
        return $query->where('tipo_veiculo_id', $tipoVeiculoId);
    }

    // Métodos de cálculo
    public function calcularAluguel(): float
    {
        if ($this->percentual_aluguel > 0) {
            return $this->fipe * ($this->percentual_aluguel / 100);
        }
        return (float) ($this->aluguel_carro ?? 0);
    }

    public function calcularProvisoesAvarias(): float
    {
        if ($this->percentual_provisoes_avarias > 0) {
            $aluguelCalculado = $this->calcularAluguel();
            return ($aluguelCalculado * $this->percentual_provisoes_avarias) / 100;
        }
        
        return $this->provisoes_avarias ?? 0;
    }

    public function calcularProvisaoDesmobilizacao(): float
    {
        if ($this->percentual_provisao_desmobilizacao > 0) {
            $provisoesAvariasCalculadas = $this->calcularProvisoesAvarias();
            return ($provisoesAvariasCalculadas * $this->percentual_provisao_desmobilizacao) / 100;
        }
        
        return $this->provisao_desmobilizacao ?? 0;
    }

    public function calcularProvisaoDiariaRac(): float
    {
        if ($this->percentual_provisao_rac > 0) {
            $aluguelCalculado = $this->calcularAluguel();
            return ($aluguelCalculado * $this->percentual_provisao_rac) / 100;
        }
        
        return $this->provisao_diaria_rac ?? 0;
    }

    public function calcularCustoTotal(): float
    {
        $aluguelCalculado = $this->calcularAluguel();
        $provisoesAvariasCalculadas = $this->calcularProvisoesAvarias();
        $provisaoDesmobilizacaoCalculada = $this->calcularProvisaoDesmobilizacao();
        $provisaoDiariaRacCalculada = $this->calcularProvisaoDiariaRac();
        
        return $this->fipe + 
               $aluguelCalculado + 
               $this->rastreador + 
               $provisoesAvariasCalculadas + 
               $provisaoDesmobilizacaoCalculada + 
               $provisaoDiariaRacCalculada;
    }

    // Accessors
    public function getAluguelCalculadoAttribute(): float
    {
        return $this->calcularAluguel();
    }

    public function getStatusFormatadoAttribute(): string
    {
        return $this->active ? 'Ativo' : 'Inativo';
    }

    public function getCustoTotalFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->custo_total, 2, ',', '.');
    }

    // Boot method para calcular automaticamente o aluguel e custo total
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($frota) {
            // Calcular aluguel automaticamente se percentual_aluguel estiver definido
            if ($frota->percentual_aluguel > 0) {
                $frota->aluguel_carro = $frota->calcularAluguel();
            }
            
            // Calcular provisões de avarias automaticamente se percentual_provisoes_avarias estiver definido
            if ($frota->percentual_provisoes_avarias > 0) {
                $frota->provisoes_avarias = $frota->calcularProvisoesAvarias();
            }
            
            // Calcular provisão desmobilização automaticamente se percentual_provisao_desmobilizacao estiver definido
            if ($frota->percentual_provisao_desmobilizacao > 0) {
                $frota->provisao_desmobilizacao = $frota->calcularProvisaoDesmobilizacao();
            }
            
            // Calcular provisão diária RAC automaticamente se percentual_provisao_rac estiver definido
            if ($frota->percentual_provisao_rac > 0) {
                $frota->provisao_diaria_rac = $frota->calcularProvisaoDiariaRac();
            }
            
            // Calcular custo total
            $frota->custo_total = $frota->calcularCustoTotal();
        });
    }
}
