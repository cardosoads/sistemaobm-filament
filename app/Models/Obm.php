<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Obm extends Model
{
    protected $table = 'obms';
    
    protected $fillable = [
        'orcamento_id',
        'colaborador_id',
        'frota_id',
        'user_id',
        'data_inicio',
        'data_fim',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'observacoes' => 'string',
    ];

    protected $dates = [
        'data_inicio',
        'data_fim',
    ];

    // Relacionamentos
    public function orcamento(): BelongsTo
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Colaborador::class, 'colaborador_id');
    }

    public function frota(): BelongsTo
    {
        return $this->belongsTo(Frota::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePendente($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeEmAndamento($query)
    {
        return $query->where('status', 'em_andamento');
    }

    public function scopeConcluida($query)
    {
        return $query->where('status', 'concluida');
    }

    public function scopeAtiva($query)
    {
        return $query->whereIn('status', ['pendente', 'em_andamento']);
    }

    public function scopePorColaborador($query, $colaboradorId)
    {
        return $query->where('colaborador_id', $colaboradorId);
    }

    public function scopePorVeiculo($query, $frotaId)
    {
        return $query->where('frota_id', $frotaId);
    }

    // Validações
    public static function validarSobreposicaoColaborador($colaboradorId, $dataInicio, $dataFim, $ignoreId = null): bool
    {
        $query = self::where('colaborador_id', $colaboradorId)
            ->where(function ($q) use ($dataInicio, $dataFim) {
                $q->where(function ($q) use ($dataInicio, $dataFim) {
                    $q->where('data_inicio', '<=', $dataInicio)
                      ->where('data_fim', '>=', $dataInicio);
                })->orWhere(function ($q) use ($dataInicio, $dataFim) {
                    $q->where('data_inicio', '<=', $dataFim)
                      ->where('data_fim', '>=', $dataFim);
                })->orWhere(function ($q) use ($dataInicio, $dataFim) {
                    $q->where('data_inicio', '>=', $dataInicio)
                      ->where('data_fim', '<=', $dataFim);
                });
            })
            ->whereIn('status', ['pendente', 'em_andamento']);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return !$query->exists();
    }

    public static function validarSobreposicaoVeiculo($frotaId, $dataInicio, $dataFim, $ignoreId = null): bool
    {
        $query = self::where('frota_id', $frotaId)
            ->where(function ($q) use ($dataInicio, $dataFim) {
                $q->where(function ($q) use ($dataInicio, $dataFim) {
                    $q->where('data_inicio', '<=', $dataInicio)
                      ->where('data_fim', '>=', $dataInicio);
                })->orWhere(function ($q) use ($dataInicio, $dataFim) {
                    $q->where('data_inicio', '<=', $dataFim)
                      ->where('data_fim', '>=', $dataFim);
                })->orWhere(function ($q) use ($dataInicio, $dataFim) {
                    $q->where('data_inicio', '>=', $dataInicio)
                      ->where('data_fim', '<=', $dataFim);
                });
            })
            ->whereIn('status', ['pendente', 'em_andamento']);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return !$query->exists();
    }

    // Acessores
    public function getPeriodoAttribute(): string
    {
        if (!$this->data_inicio || !$this->data_fim) {
            return '';
        }
        return $this->data_inicio->format('d/m/Y') . ' - ' . $this->data_fim->format('d/m/Y');
    }

    public function getDuracaoDiasAttribute(): int
    {
        if (!$this->data_inicio || !$this->data_fim) {
            return 0;
        }
        return $this->data_inicio->diffInDays($this->data_fim) + 1;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pendente' => 'Pendente',
            'em_andamento' => 'Em Andamento',
            'concluida' => 'Concluída',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pendente' => 'warning',
            'em_andamento' => 'primary',
            'concluida' => 'success',
            default => 'gray',
        };
    }



    public function podeIniciar(): bool
    {
        return $this->status === 'pendente' && 
               $this->colaborador_id && 
               $this->frota_id &&
               ($this->data_inicio ? $this->data_inicio <= now() : false);
    }

    public function iniciar(): bool
    {
        if ($this->podeIniciar()) {
            $this->status = 'em_andamento';
            return $this->save();
        }
        return false;
    }

    public function concluir(): bool
    {
        if ($this->status === 'em_andamento') {
            $this->status = 'concluida';
            return $this->save();
        }
        return false;
    }
}
