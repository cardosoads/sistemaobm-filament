<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TipoVeiculo extends Model
{
    use HasFactory;

    /**
     * Nome da tabela no banco de dados.
     */
    protected $table = 'tipos_veiculos';

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'codigo',
        'consumo_km_litro',
        'tipo_combustivel',
        'descricao',
        'active',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     */
    protected $casts = [
        'consumo_km_litro' => 'decimal:2',
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
     * Tipos de combustível disponíveis.
     */
    public const TIPOS_COMBUSTIVEL = [
        'Gasolina' => 'Gasolina',
        'Etanol' => 'Etanol',
        'Diesel' => 'Diesel',
        'Flex' => 'Flex',
    ];

    /**
     * Scope para filtrar apenas tipos de veículos ativos.
     */
    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope para filtrar apenas tipos de veículos inativos.
     */
    public function scopeInativos(Builder $query): Builder
    {
        return $query->where('active', false);
    }

    /**
     * Scope para filtrar por tipo de combustível.
     */
    public function scopePorTipoCombustivel(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo_combustivel', $tipo);
    }

    /**
     * Scope para buscar por código ou descrição.
     */
    public function scopeBuscar(Builder $query, string $termo): Builder
    {
        return $query->where(function (Builder $q) use ($termo) {
            $q->where('codigo', 'like', "%{$termo}%")
              ->orWhere('descricao', 'like', "%{$termo}%");
        });
    }

    /**
     * Accessor para verificar se o tipo de veículo está ativo.
     */
    public function getAtivoAttribute(): bool
    {
        return (bool) $this->active;
    }

    /**
     * Accessor para formatar o consumo.
     */
    public function getConsumoFormatadoAttribute(): string
    {
        return number_format($this->consumo_km_litro, 2, ',', '.') . ' km/l';
    }

    /**
     * Accessor para obter o status formatado.
     */
    public function getStatusAttribute(): string
    {
        return $this->active ? 'Ativo' : 'Inativo';
    }

    /**
     * Verifica se o tipo de veículo está ativo.
     */
    public function isAtivo(): bool
    {
        return $this->active === true;
    }

    /**
     * Ativa o tipo de veículo.
     */
    public function ativar(): bool
    {
        return $this->update(['active' => true]);
    }

    /**
     * Desativa o tipo de veículo.
     */
    public function desativar(): bool
    {
        return $this->update(['active' => false]);
    }

    /**
     * Alterna o status do tipo de veículo.
     */
    public function alternarStatus(): bool
    {
        return $this->update(['active' => !$this->active]);
    }

    /**
     * Obtém os tipos de combustível disponíveis.
     */
    public static function getTiposCombustivel(): array
    {
        return self::TIPOS_COMBUSTIVEL;
    }

    /**
     * Representação em string do modelo.
     */
    public function __toString(): string
    {
        return "{$this->codigo} - {$this->descricao}";
    }

    /**
     * Configuração para serialização JSON.
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        
        // Adiciona campos computados
        $array['ativo'] = $this->ativo;
        $array['consumo_formatado'] = $this->consumo_formatado;
        $array['status'] = $this->status;
        
        return $array;
    }
}