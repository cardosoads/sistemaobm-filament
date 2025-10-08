<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Combustivel extends Model
{
    use HasFactory;

    /**
     * Nome da tabela no banco de dados.
     */
    protected $table = 'combustivels';

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'base_id',
        'convenio',
        'preco_litro',
        'active',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     */
    protected $casts = [
        'preco_litro' => 'decimal:3',
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Valores padrão para os atributos.
     */
    protected $attributes = [
        'active' => true,
    ];

    /**
     * Relacionamento com Base.
     */
    public function base(): BelongsTo
    {
        return $this->belongsTo(Base::class);
    }

    /**
     * Scope para filtrar apenas combustíveis ativos.
     */
    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope para filtrar apenas combustíveis inativos.
     */
    public function scopeInativos(Builder $query): Builder
    {
        return $query->where('active', false);
    }

    /**
     * Scope para buscar por convênio.
     */
    public function scopeBuscar(Builder $query, string $termo): Builder
    {
        return $query->where('convenio', 'like', "%{$termo}%");
    }

    /**
     * Scope para filtrar por base.
     */
    public function scopePorBase(Builder $query, int $baseId): Builder
    {
        return $query->where('base_id', $baseId);
    }

    /**
     * Accessor para verificar se o combustível está ativo.
     */
    public function getAtivoAttribute(): bool
    {
        return (bool) $this->active;
    }

    /**
     * Accessor para formatar o preço.
     */
    public function getPrecoFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->preco_litro, 3, ',', '.');
    }

    /**
     * Accessor para obter o status formatado.
     */
    public function getStatusAttribute(): string
    {
        return $this->active ? 'Ativo' : 'Inativo';
    }

    /**
     * Verifica se o combustível está ativo.
     */
    public function isAtivo(): bool
    {
        return $this->active === true;
    }

    /**
     * Ativa o combustível.
     */
    public function ativar(): bool
    {
        return $this->update(['active' => true]);
    }

    /**
     * Desativa o combustível.
     */
    public function desativar(): bool
    {
        return $this->update(['active' => false]);
    }

    /**
     * Alterna o status do combustível.
     */
    public function alternarStatus(): bool
    {
        return $this->update(['active' => !$this->active]);
    }

    /**
     * Representação em string do modelo.
     */
    public function __toString(): string
    {
        return "{$this->convenio} - {$this->base->base}";
    }
}