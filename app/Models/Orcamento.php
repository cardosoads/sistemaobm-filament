<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Orcamento extends Model
{
    protected $table = 'orcamentos';
    
    protected $fillable = [
        'data_solicitacao',
        'centro_custo_id',
        'id_protocolo',
        'cliente_omie_id',
        'numero_orcamento',
        'nome_rota',
        'id_logcare',
        'cliente_nome',
        'fornecedor_omie_id',
        'horario',
        'frequencia_atendimento',
        'tipo_orcamento',
        'user_id',
        'data_orcamento',
        'valor_total',
        'valor_impostos',
        'valor_final',
        'status',
        'observacoes',
        'grupo_imposto_id',
        'grupo_imposto_id_aumento',
        'grupo_imposto_id_rota',
        'recurso_humano_id',
        'base_id',
        'frota_id',
        // Campos da Nova Rota
        'incluir_funcionario',
        'incluir_frota',
        'incluir_prestador',
        'valor_funcionario',
        'frota_id',
        'valor_aluguel_frota',
        'fornecedor_omie_id_rota',
        'fornecedor_referencia',
        'fornecedor_dias',
        'lucro_percentual_rota',
        'impostos_percentual_rota',
    ];

    protected $casts = [
        'data_solicitacao' => 'date',
        'data_orcamento' => 'date',
        'valor_total' => 'decimal:2',
        'valor_impostos' => 'decimal:2',
        'valor_final' => 'decimal:2',
        'frequencia_atendimento' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($orcamento) {
            if (empty($orcamento->numero_orcamento)) {
                $orcamento->numero_orcamento = self::gerarNumeroOrcamento();
            }
            if (empty($orcamento->data_orcamento)) {
                $orcamento->data_orcamento = now();
            }
        });

        static::saving(function ($orcamento) {
            $orcamento->calcularValoresTotais();
        });
    }

    public static function gerarNumeroOrcamento(): string
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        $proximoNumero = $ultimo ? ((int) substr($ultimo->numero_orcamento, -6)) + 1 : 1;
        return 'ORC' . date('Y') . str_pad($proximoNumero, 6, '0', STR_PAD_LEFT);
    }

    public function calcularValoresTotais(): void
    {
        switch ($this->tipo_orcamento) {
            case 'prestador':
                $this->valor_total = $this->prestadores()->sum('valor_total');
                $this->valor_impostos = $this->prestadores()->sum('valor_impostos');
                break;
            case 'aumento_km':
                $this->valor_total = $this->aumentosKm()->sum('valor_final');
                $this->valor_impostos = $this->aumentosKm()->sum('valor_impostos');
                break;
            case 'proprio_nova_rota':
                $this->valor_total = $this->propriosNovaRota()->sum('valor_final');
                $this->valor_impostos = $this->propriosNovaRota()->sum('valor_impostos');
                break;
        }
        
        $this->valor_final = $this->valor_total;
    }

    public function centroCusto(): BelongsTo
    {
        return $this->belongsTo(CentroCusto::class, 'centro_custo_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function prestadores(): HasMany
    {
        return $this->hasMany(OrcamentoPrestador::class, 'orcamento_id');
    }

    public function aumentosKm(): HasMany
    {
        return $this->hasMany(OrcamentoAumentoKm::class, 'orcamento_id');
    }

    public function propriosNovaRota(): HasMany
    {
        return $this->hasMany(OrcamentoProprioNovaRota::class, 'orcamento_id');
    }

    public function grupoImposto(): BelongsTo
    {
        return $this->belongsTo(GrupoImposto::class, 'grupo_imposto_id');
    }

    public function recursoHumano(): BelongsTo
    {
        return $this->belongsTo(RecursoHumano::class, 'recurso_humano_id');
    }

    public function base(): BelongsTo
    {
        return $this->belongsTo(Base::class, 'base_id');
    }

    public function frota(): BelongsTo
    {
        return $this->belongsTo(Frota::class, 'frota_id');
    }

    public function clienteFornecedor(): BelongsTo
    {
        return $this->belongsTo(ClienteFornecedor::class, 'cliente_omie_id');
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(ClienteFornecedor::class, 'fornecedor_omie_id', 'codigo_cliente_omie');
    }

    public function fornecedorRota(): BelongsTo
    {
        return $this->belongsTo(ClienteFornecedor::class, 'fornecedor_omie_id_rota', 'codigo_cliente_omie');
    }

    public function scopeEmAndamento($query)
    {
        return $query->where('status', 'em_andamento');
    }

    public function scopeEnviado($query)
    {
        return $query->where('status', 'enviado');
    }

    public function scopeAprovado($query)
    {
        return $query->where('status', 'aprovado');
    }

    public function scopeRejeitado($query)
    {
        return $query->where('status', 'rejeitado');
    }

    public function scopeCancelado($query)
    {
        return $query->where('status', 'cancelado');
    }

    public function getStatusFormatadoAttribute(): string
    {
        return match($this->status) {
            'em_andamento' => 'Em Andamento',
            'enviado' => 'Enviado',
            'aprovado' => 'Aprovado',
            'rejeitado' => 'Rejeitado',
            'cancelado' => 'Cancelado',
            default => $this->status,
        };
    }

    public function getFrequenciaAtendimentoFormatadaAttribute(): string
    {
        if (!$this->frequencia_atendimento) {
            return '';
        }

        $diasSemana = [
            'seg' => 'Seg',
            'ter' => 'Ter',
            'qua' => 'Qua',
            'qui' => 'Qui',
            'sex' => 'Sex',
            'sab' => 'Sáb',
            'dom' => 'Dom',
        ];

        $diasSelecionados = array_map(function($dia) use ($diasSemana) {
            return $diasSemana[$dia] ?? $dia;
        }, $this->frequencia_atendimento);

        return implode(', ', $diasSelecionados);
    }

    public function calcularValorTotal(): float
    {
        return match($this->tipo_orcamento) {
            'prestador' => $this->prestadores()->sum('valor_total'),
            'aumento_km' => $this->aumentosKm()->sum('valor_final'),
            'proprio_nova_rota' => $this->propriosNovaRota()->sum('valor_final'),
            default => 0,
        };
    }

    public function calcularValorImpostos(): float
    {
        return match($this->tipo_orcamento) {
            'prestador' => $this->prestadores()->sum('valor_impostos'),
            'aumento_km' => $this->aumentosKm()->sum('valor_impostos'),
            'proprio_nova_rota' => $this->propriosNovaRota()->sum('valor_impostos'),
            default => 0,
        };
    }
}