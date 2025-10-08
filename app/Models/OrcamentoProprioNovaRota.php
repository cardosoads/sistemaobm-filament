<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrcamentoProprioNovaRota extends Model
{
    protected $table = 'orcamento_proprio_nova_rota';
    
    protected $fillable = [
        'orcamento_id',
        'incluir_funcionario',
        'incluir_frota',
        'incluir_fornecedor',
        'recurso_humano_id',
        'base_id',
        'valor_funcionario',
        'frota_id',
        'valor_aluguel_frota',
        'quantidade_dias',
        'valor_combustivel',
        'valor_pedagio',
        'fornecedor_omie_id',
        'fornecedor_nome',
        'fornecedor_referencia',
        'fornecedor_dias',
        'fornecedor_custo',
        'fornecedor_lucro',
        'fornecedor_impostos',
        'fornecedor_total',
        'valor_total_rotas',
        'valor_total_geral',
        'lucro_percentual',
        'valor_lucro',
        'impostos_percentual',
        'valor_impostos',
        'valor_final',
        'grupo_imposto_id',
    ];

    protected $casts = [
        'incluir_funcionario' => 'boolean',
        'incluir_frota' => 'boolean',
        'incluir_fornecedor' => 'boolean',
        'valor_funcionario' => 'decimal:2',
        'valor_aluguel_frota' => 'decimal:2',
        'quantidade_dias' => 'integer',
        'valor_combustivel' => 'decimal:2',
        'valor_pedagio' => 'decimal:2',
        'fornecedor_referencia' => 'decimal:2',
        'fornecedor_dias' => 'integer',
        'fornecedor_custo' => 'decimal:2',
        'fornecedor_lucro' => 'decimal:2',
        'fornecedor_impostos' => 'decimal:2',
        'fornecedor_total' => 'decimal:2',
        'valor_total_rotas' => 'decimal:2',
        'valor_total_geral' => 'decimal:2',
        'lucro_percentual' => 'decimal:2',
        'valor_lucro' => 'decimal:2',
        'impostos_percentual' => 'decimal:2',
        'valor_impostos' => 'decimal:2',
        'valor_final' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($proprioNovaRota) {
            \Log::info('OrcamentoProprioNovaRota - CREATING:', $proprioNovaRota->toArray());
        });

        static::created(function ($proprioNovaRota) {
            \Log::info('OrcamentoProprioNovaRota - CREATED:', $proprioNovaRota->toArray());
        });

        static::updating(function ($proprioNovaRota) {
            \Log::info('OrcamentoProprioNovaRota - UPDATING:', $proprioNovaRota->toArray());
        });

        static::updated(function ($proprioNovaRota) {
            \Log::info('OrcamentoProprioNovaRota - UPDATED:', $proprioNovaRota->toArray());
        });

        static::saving(function ($proprioNovaRota) {
            \Log::info('OrcamentoProprioNovaRota - SAVING (antes do cálculo):', $proprioNovaRota->toArray());
            $proprioNovaRota->calcularValores();
            \Log::info('OrcamentoProprioNovaRota - SAVING (após o cálculo):', $proprioNovaRota->toArray());
        });
    }

    public function calcularValores(): void
    {
        // Cálculo do custo do fornecedor
        $this->fornecedor_custo = $this->fornecedor_referencia * $this->fornecedor_dias;
        
        // Cálculo do lucro do fornecedor
        $this->fornecedor_lucro = $this->fornecedor_custo * ($this->lucro_percentual / 100);
        
        // Cálculo dos impostos do fornecedor
        $base_impostos_fornecedor = $this->fornecedor_custo + $this->fornecedor_lucro;
        $this->fornecedor_impostos = $base_impostos_fornecedor * ($this->impostos_percentual / 100);
        
        // Valor total do fornecedor
        $this->fornecedor_total = $base_impostos_fornecedor + $this->fornecedor_impostos;
        
        // Valor total geral (funcionário + frota + fornecedor)
        $this->valor_total_geral = 
            $this->valor_funcionario +
            $this->valor_aluguel_frota +
            $this->fornecedor_total;
        
        // Cálculo do lucro geral (sobre o total)
        $this->valor_lucro = $this->valor_total_geral * ($this->lucro_percentual / 100);
        
        // Cálculo dos impostos gerais
        $base_impostos = $this->valor_total_geral + $this->valor_lucro;
        $this->valor_impostos = $base_impostos * ($this->impostos_percentual / 100);
        
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

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(ClienteFornecedor::class, 'fornecedor_omie_id', 'codigo_cliente_omie');
    }
}