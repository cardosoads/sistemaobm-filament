<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ClienteFornecedor;

class OrcamentoPrestador extends Model
{
    protected $table = 'orcamento_prestador';
    
    protected $fillable = [
        'orcamento_id',
        'fornecedor_omie_id',
        'fornecedor_nome',
        'valor_referencia',
        'qtd_dias',
        'custo_fornecedor',
        'lucro_percentual',
        'valor_lucro',
        'impostos_percentual',
        'valor_impostos',
        'valor_total',
        'grupo_imposto_id',
    ];

    protected $casts = [
        'valor_referencia' => 'decimal:2',
        'custo_fornecedor' => 'decimal:2',
        'lucro_percentual' => 'decimal:2',
        'valor_lucro' => 'decimal:2',
        'impostos_percentual' => 'decimal:2',
        'valor_impostos' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($prestador) {
            $prestador->calcularValores();
        });
    }

    public function calcularValores(): void
    {
        // Cálculo baseado na frequência do orçamento
        $dias = $this->calcularDias();
        
        // Cálculo do custo do fornecedor
        $this->custo_fornecedor = $this->valor_referencia * $dias;
        
        // Cálculo do lucro
        $this->valor_lucro = $this->custo_fornecedor * ($this->lucro_percentual / 100);
        
        // Cálculo dos impostos (sobre custo + lucro)
        $base_impostos = $this->custo_fornecedor + $this->valor_lucro;
        
        // Usar o percentual total do grupo de impostos se disponível
        if ($this->grupoImposto) {
            $this->valor_impostos = $this->grupoImposto->calcularValorImpostos($base_impostos);
        } else {
            // Fallback para o campo impostos_percentual se não houver grupo
            $this->valor_impostos = $base_impostos * (($this->impostos_percentual ?? 0) / 100);
        }
        
        // Valor total
        $this->valor_total = $base_impostos + $this->valor_impostos;
    }

    public function calcularDias(): int
    {
        if (!$this->orcamento) {
            return $this->qtd_dias ?? 1;
        }

        return match($this->orcamento->frequencia_atendimento) {
            'diario' => $this->qtd_dias * 30,
            'semanal' => $this->qtd_dias * 4,
            'quinzenal' => $this->qtd_dias * 2,
            'mensal' => $this->qtd_dias,
            default => $this->qtd_dias ?? 1,
        };
    }

    public function orcamento(): BelongsTo
    {
        return $this->belongsTo(Orcamento::class, 'orcamento_id');
    }

    public function grupoImposto(): BelongsTo
    {
        return $this->belongsTo(GrupoImposto::class, 'grupo_imposto_id');
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(ClienteFornecedor::class, 'fornecedor_omie_id', 'codigo_cliente_omie');
    }
}