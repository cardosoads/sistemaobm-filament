<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Funcionario extends Model
{
    use HasFactory;

    /**
     * Nome da tabela
     */
    protected $table = 'funcionarios';

    /**
     * Campos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'nome',
        'cpf',
        'rg',
        'data_nascimento',
        'telefone',
        'email',
        'data_admissao',
        'cargo_id',
        'base_id',
        'status',
    ];

    /**
     * Casting de tipos
     */
    protected $casts = [
        'data_nascimento' => 'date',
        'data_admissao' => 'date',
        'status' => 'boolean',
    ];

    /**
     * Relacionamento com Cargo (antigo RecursoHumano)
     */
    public function cargo(): BelongsTo
    {
        return $this->belongsTo(RecursoHumano::class, 'cargo_id');
    }

    /**
     * Relacionamento com Base
     */
    public function base(): BelongsTo
    {
        return $this->belongsTo(Base::class, 'base_id');
    }

    /**
     * Scope para funcionários ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope por cargo
     */
    public function scopePorCargo($query, $cargoId)
    {
        return $query->where('cargo_id', $cargoId);
    }

    /**
     * Scope por base
     */
    public function scopePorBase($query, $baseId)
    {
        return $query->where('base_id', $baseId);
    }
}