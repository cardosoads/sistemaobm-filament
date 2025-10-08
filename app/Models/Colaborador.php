<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Colaborador extends Model
{
    use HasFactory;

    protected $table = 'colaboradores';

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

    protected $casts = [
        'data_nascimento' => 'date',
        'data_admissao' => 'date',
        'status' => 'boolean',
    ];

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(RecursoHumano::class, 'cargo_id');
    }

    public function base(): BelongsTo
    {
        return $this->belongsTo(Base::class);
    }

    /**
     * Scope para buscar apenas colaboradores ativos
     */
    public function scopeAtivo($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope para buscar por cargo
     */
    public function scopePorCargo($query, $cargoId)
    {
        return $query->where('cargo_id', $cargoId);
    }

    /**
     * Scope para buscar por base
     */
    public function scopePorBase($query, $baseId)
    {
        return $query->where('base_id', $baseId);
    }
}
