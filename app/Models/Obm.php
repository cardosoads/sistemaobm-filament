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
        // Campos consolidados do orçamento
        'nome_rota',
        'cliente_nome',
        'origem',
        'destino',
        'valor_final',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'observacoes' => 'string',
        'valor_final' => 'decimal:2',
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

    // -------------------------
    // Consolidação de dados
    // -------------------------
    public function consolidarDadosOrcamento(): void
    {
        $orcamento = $this->orcamento;
        if (!$orcamento) {
            return;
        }

        $this->nome_rota = $orcamento->nome_rota;
        $this->cliente_nome = $orcamento->cliente_nome ?? optional($orcamento->clienteFornecedor)->nome;
        $this->valor_final = $orcamento->valor_final;

        // Origem/Destino podem não existir; manter seguros
        // Como o projeto removeu origem/destino do modelo de nova rota, deixamos nulos
        $this->origem = $orcamento->origem ?? null;
        $this->destino = $orcamento->destino ?? null;

        $this->save();
    }

    // -------------------------
    // Métodos auxiliares existentes (resumo do período e status)
    // -------------------------
    public function getPeriodoAttribute(): string
    {
        if (!$this->data_inicio || !$this->data_fim) {
            return '-';
        }
        return $this->data_inicio->format('d/m/Y') . ' a ' . $this->data_fim->format('d/m/Y');
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
        return match ($this->status) {
            'pendente' => 'Pendente',
            'em_andamento' => 'Em Andamento',
            'concluida' => 'Concluída',
            default => ucfirst($this->status ?? 'Indefinido'),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pendente' => 'warning',
            'em_andamento' => 'info',
            'concluida' => 'success',
            default => 'gray',
        };
    }

    public function podeIniciar(): bool
    {
        return $this->status === 'pendente';
    }

    public function iniciar(): bool
    {
        if (!$this->podeIniciar()) {
            return false;
        }
        $this->status = 'em_andamento';
        return $this->save();
    }

    public function concluir(): bool
    {
        if ($this->status !== 'em_andamento') {
            return false;
        }
        $this->status = 'concluida';
        return $this->save();
    }

    // -------------------------
    // Validações de sobreposição
    // -------------------------
    public static function validarSobreposicaoColaborador(int $colaboradorId, $dataInicio, $dataFim = null, ?int $ignorarObmId = null): bool
    {
        $inicio = $dataInicio instanceof Carbon ? $dataInicio : Carbon::parse($dataInicio);
        $fim = $dataFim ? ($dataFim instanceof Carbon ? $dataFim : Carbon::parse($dataFim)) : null;

        $query = static::query()
            ->where('colaborador_id', $colaboradorId)
            ->whereIn('status', ['pendente', 'em_andamento']);

        if ($ignorarObmId) {
            $query->where('id', '!=', $ignorarObmId);
        }

        $query->where(function ($q) use ($inicio, $fim) {
            if ($fim) {
                $q->whereDate('data_inicio', '<=', $fim);
            }

            $q->where(function ($q2) use ($inicio) {
                $q2->whereNull('data_fim')
                    ->orWhereDate('data_fim', '>=', $inicio);
            });
        });

        $existeSobreposicao = $query->exists();

        return !$existeSobreposicao; // true quando NÃO há sobreposição
    }

    public static function validarSobreposicaoVeiculo(int $frotaId, $dataInicio, $dataFim = null, ?int $ignorarObmId = null): bool
    {
        $inicio = $dataInicio instanceof Carbon ? $dataInicio : Carbon::parse($dataInicio);
        $fim = $dataFim ? ($dataFim instanceof Carbon ? $dataFim : Carbon::parse($dataFim)) : null;

        $query = static::query()
            ->where('frota_id', $frotaId)
            ->whereIn('status', ['pendente', 'em_andamento']);

        if ($ignorarObmId) {
            $query->where('id', '!=', $ignorarObmId);
        }

        $query->where(function ($q) use ($inicio, $fim) {
            if ($fim) {
                $q->whereDate('data_inicio', '<=', $fim);
            }

            $q->where(function ($q2) use ($inicio) {
                $q2->whereNull('data_fim')
                    ->orWhereDate('data_fim', '>=', $inicio);
            });
        });

        $existeSobreposicao = $query->exists();

        return !$existeSobreposicao; // true quando NÃO há sobreposição
    }
}
