<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\GrupoImposto;

class RecursoHumano extends Model
{
    use HasFactory;

    /**
     * Nome da tabela
     */
    protected $table = 'cargos';

    /**
     * Campos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'tipo_contratacao',
        'cargo',
        'base_id',
        'base_salarial',
        'salario_base',
        'insalubridade',
        'periculosidade',
        'horas_extras',
        'adicional_noturno',
        'extras',
        'vale_transporte',
        'beneficios',
        'encargos_sociais',
        'custo_total_mao_obra',
        'percentual_encargos',
        'percentual_beneficios',
        'grupo_imposto_id',
        'active',
    ];

    /**
     * Casting de tipos
     */
    protected $casts = [
        'base_salarial' => 'decimal:2',
        'salario_base' => 'decimal:2',
        'insalubridade' => 'decimal:2',
        'periculosidade' => 'decimal:2',
        'horas_extras' => 'decimal:2',
        'adicional_noturno' => 'decimal:2',
        'extras' => 'decimal:2',
        'vale_transporte' => 'decimal:2',
        'beneficios' => 'decimal:2',
        'encargos_sociais' => 'decimal:2',
        'custo_total_mao_obra' => 'decimal:2',
        'percentual_encargos' => 'decimal:2',
        'percentual_beneficios' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Relacionamento com Base
     */
    public function base(): BelongsTo
    {
        return $this->belongsTo(Base::class);
    }

    /**
     * Relacionamento com GrupoImposto
     */
    public function grupoImposto(): BelongsTo
    {
        return $this->belongsTo(GrupoImposto::class);
    }

    /**
     * Scope para recursos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope por cargo
     */
    public function scopeByCargo($query, $cargo)
    {
        return $query->where('cargo', $cargo);
    }

    /**
     * Scope por tipo de contratação
     */
    public function scopeByTipoContratacao($query, $tipo)
    {
        return $query->where('tipo_contratacao', $tipo);
    }

    /**
     * Calcula o total de adicionais salariais
     */
    public function getTotalAdicionaisAttribute(): float
    {
        return $this->insalubridade + 
               $this->periculosidade + 
               $this->horas_extras + 
               $this->adicional_noturno + 
               $this->extras + 
               $this->vale_transporte;
    }

    /**
     * Calcula o salário bruto (base + adicionais)
     */
    public function getSalarioBrutoAttribute(): float
    {
        return $this->salario_base + $this->total_adicionais;
    }

    /**
     * Calcula automaticamente os encargos sociais
     */
    public function calcularEncargosSociais(): float
    {
        if ($this->percentual_encargos > 0) {
            return $this->salario_bruto * ($this->percentual_encargos / 100);
        }
        return (float) ($this->encargos_sociais ?? 0);
    }

    /**
     * Calcula automaticamente os benefícios
     */
    public function calcularBeneficios(): float
    {
        if ($this->percentual_beneficios > 0) {
            return $this->salario_bruto * ($this->percentual_beneficios / 100);
        }
        return (float) ($this->beneficios ?? 0);
    }

    /**
     * Calcula o custo total da mão de obra
     */
    public function calcularCustoTotal(): float
    {
        return $this->salario_bruto + 
               $this->calcularEncargosSociais() + 
               ($this->beneficios ?? 0);
    }

    /**
     * Atualiza automaticamente os campos calculados
     * Só recalcula se os percentuais estiverem definidos
     */
    public function atualizarCalculos(): void
    {
        // Só recalcula encargos se há percentual definido
        if ($this->percentual_encargos > 0) {
            $this->encargos_sociais = $this->calcularEncargosSociais();
        }
        
        // Só recalcula benefícios se há percentual definido
        if ($this->percentual_beneficios > 0) {
            $this->beneficios = $this->calcularBeneficios();
        }
        
        // Sempre recalcula o custo total
        $this->custo_total_mao_obra = $this->calcularCustoTotal();
    }

    /**
     * Boot do modelo
     */
    protected static function boot()
    {
        parent::boot();
        
        // Removido o cálculo automático para evitar duplicação de valores
        // Os cálculos devem ser feitos manualmente quando necessário
    }

    /**
     * Tipos de contratação disponíveis
     */
    public static function getTiposContratacao(): array
    {
        return [
            'CLT' => 'CLT',
            'PJ' => 'Pessoa Jurídica',
            'Terceirizado' => 'Terceirizado',
            'Temporário' => 'Temporário',
        ];
    }

    /**
     * Cargos disponíveis
     */
    public static function getCargos(): array
    {
        return [
            'Motorista' => 'Motorista',
            'Motorista Líder' => 'Motorista Líder',
            'Ajudante' => 'Ajudante',
            'Supervisor' => 'Supervisor',
        ];
    }
}