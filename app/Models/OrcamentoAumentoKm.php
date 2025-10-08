<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrcamentoAumentoKm extends Model
{
    protected $table = 'orcamento_aumento_km';
    
    protected $fillable = [
        'orcamento_id',
        'km_por_dia',
        'quantidade_dias_aumento',
        'combustivel_km_litro',
        'valor_combustivel',
        'hora_extra',
        'pedagio',
        'valor_total',
        'percentual_lucro',
        'valor_lucro',
        'percentual_impostos',
        'valor_impostos',
        'valor_final',
        'grupo_imposto_id',
    ];

    protected $casts = [
        'km_por_dia' => 'decimal:2',
        'quantidade_dias_aumento' => 'integer',
        'combustivel_km_litro' => 'decimal:2',
        'valor_combustivel' => 'decimal:2',
        'hora_extra' => 'decimal:2',
        'pedagio' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'percentual_lucro' => 'decimal:2',
        'valor_lucro' => 'decimal:2',
        'percentual_impostos' => 'decimal:2',
        'valor_impostos' => 'decimal:2',
        'valor_final' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($aumentoKm) {
            $aumentoKm->calcularValores();
        });
    }

    public function calcularValores(): void
    {
        // Cálculo do valor total base
        $km_total = $this->km_por_dia * $this->quantidade_dias_aumento;
        $litros_necessarios = $km_total / $this->combustivel_km_litro;
        $custo_combustivel = $litros_necessarios * $this->valor_combustivel;
        
        $this->valor_total = $custo_combustivel + $this->hora_extra + $this->pedagio;
        
        // Cálculo do lucro
        $this->valor_lucro = $this->valor_total * ($this->percentual_lucro / 100);
        
        // Cálculo dos impostos (sobre total + lucro)
        $base_impostos = $this->valor_total + $this->valor_lucro;
        $this->valor_impostos = $base_impostos * ($this->percentual_impostos / 100);
        
        // Valor final
        $this->valor_final = $base_impostos + $this->valor_impostos;
    }

    public function orcamento(): BelongsTo
    {
        return $this->belongsTo(Orcamento::class, 'orcamento_id');
    }

    public function grupoImposto(): BelongsTo
    {
        return $this->belongsTo(GrupoImposto::class, 'grupo_imposto_id');
    }
}