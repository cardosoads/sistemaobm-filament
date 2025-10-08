<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Imposto extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
        'percentual',
        'ativo',
    ];

    protected $casts = [
        'percentual' => 'decimal:4',
        'ativo' => 'boolean',
    ];

    /**
     * Relacionamento many-to-many com grupos de impostos
     */
    public function gruposImpostos(): BelongsToMany
    {
        return $this->belongsToMany(GrupoImposto::class, 'grupo_imposto_imposto');
    }

    /**
     * Scope para buscar apenas impostos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Retorna o percentual formatado
     */
    public function getPercentualFormatadoAttribute(): string
    {
        return number_format($this->percentual, 2, ',', '.') . '%';
    }

    /**
     * Retorna o status formatado
     */
    public function getStatusAttribute(): string
    {
        return $this->ativo ? 'Ativo' : 'Inativo';
    }

    /**
     * Calcula o valor do imposto sobre um valor base
     */
    public function calcularValor(float $valorBase): float
    {
        return ($valorBase * $this->percentual) / 100;
    }

    /**
     * Retorna o valor do imposto formatado
     */
    public function calcularValorFormatado(float $valorBase): string
    {
        $valor = $this->calcularValor($valorBase);
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }
}
