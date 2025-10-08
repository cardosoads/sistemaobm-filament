<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Veiculo extends Model
{
    protected $fillable = [
        'placa',
        'renavam',
        'chassi',
        'ano_modelo',
        'cor',
        'marca_modelo',
        'tipo_combustivel',
        'status',
        'tipo_veiculo_id',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function tipoVeiculo(): BelongsTo
    {
        return $this->belongsTo(TipoVeiculo::class);
    }
}
