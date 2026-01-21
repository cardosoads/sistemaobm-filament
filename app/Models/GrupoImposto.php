<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoImposto extends Model
{
    protected $table = 'grupos_impostos';
    
    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /**
     * Relacionamento many-to-many com impostos
     */
    public function impostos(): BelongsToMany
    {
        return $this->belongsToMany(Imposto::class, 'grupo_imposto_imposto');
    }

    /**
     * Calcula o percentual total dos impostos associados
     */
    public function getPercentualTotalAttribute(): float
    {
        return $this->impostos()->where('ativo', true)->sum('percentual');
    }

    /**
     * Retorna o percentual total formatado
     */
    public function getPercentualTotalFormatadoAttribute(): string
    {
        return number_format($this->percentual_total, 2, ',', '.') . '%';
    }

    /**
     * Calcula o valor dos impostos sobre um valor base
     */
    public function calcularValorImpostos(float $valorBase): float
    {
        return ($valorBase * $this->percentual_total) / 100;
    }

    /**
     * Retorna o valor dos impostos formatado
     */
    public function calcularValorImpostosFormatado(float $valorBase): string
    {
        $valor = $this->calcularValorImpostos($valorBase);
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }

    /**
     * Scope para buscar apenas grupos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function orcamentosPrestador(): HasMany
    {
        return $this->hasMany(OrcamentoPrestador::class, 'grupo_imposto_id');
    }

    public function orcamentosAumentoKm(): HasMany
    {
        return $this->hasMany(OrcamentoAumentoKm::class, 'grupo_imposto_id');
    }

    public function orcamentosProprioNovaRota(): HasMany
    {
        return $this->hasMany(OrcamentoProprioNovaRota::class, 'grupo_imposto_id');
    }
}