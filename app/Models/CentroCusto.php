<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ClienteFornecedor;
use App\Models\Base;

class CentroCusto extends Model
{
    use HasFactory;

    protected $table = 'centros_custo';

    protected $fillable = [
        'codigo_departamento_omie',
        'codigo_departamento_integracao',
        'nome',
        'descricao',
        'cliente_id',
        'base_id',
        'supervisor',
        'inativo',
        'importado_api',
        'dados_originais_api',
        'ultima_sincronizacao',
        'status_sincronizacao',
        'data_inclusao',
        'data_alteracao',
    ];

    protected $casts = [
        'dados_originais_api' => 'array',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'ultima_sincronizacao' => 'datetime',
    ];

    protected $dates = [
        'data_inclusao',
        'data_alteracao',
        'ultima_sincronizacao',
        'created_at',
        'updated_at',
    ];

    // Scopes para filtrar centros de custo
    public function scopeAtivos($query)
    {
        return $query->where('inativo', 'N');
    }

    public function scopeInativos($query)
    {
        return $query->where('inativo', 'S');
    }

    public function scopeSincronizados($query)
    {
        return $query->where('status_sincronizacao', 'sincronizado');
    }

    public function scopePendentes($query)
    {
        return $query->where('status_sincronizacao', 'pendente');
    }

    public function scopeComErro($query)
    {
        return $query->where('status_sincronizacao', 'erro');
    }

    public function scopeImportadosApi($query)
    {
        return $query->where('importado_api', 'S');
    }

    public function scopeManuais($query)
    {
        return $query->where('importado_api', 'N');
    }

    // Accessors
    public function getStatusAttribute()
    {
        return $this->inativo === 'N' ? 'Ativo' : 'Inativo';
    }

    public function getTipoImportacaoAttribute()
    {
        return $this->importado_api === 'S' ? 'API' : 'Manual';
    }

    // Métodos para sincronização
    public function marcarComoSincronizado()
    {
        $this->update([
            'status_sincronizacao' => 'sincronizado',
            'ultima_sincronizacao' => now(),
        ]);
    }

    public function marcarComoErro()
    {
        $this->update([
            'status_sincronizacao' => 'erro',
        ]);
    }

    public function marcarComoPendente()
    {
        $this->update([
            'status_sincronizacao' => 'pendente',
        ]);
    }

    // Método para buscar por código Omie
    public static function findByCodigoOmie($codigo)
    {
        return static::where('codigo_departamento_omie', $codigo)->first();
    }

    // Método para buscar por código de integração
    public static function findByCodigoIntegracao($codigo)
    {
        return static::where('codigo_departamento_integracao', $codigo)->first();
    }

    // Relacionamentos
    public function cliente()
    {
        return $this->belongsTo(ClienteFornecedor::class, 'cliente_id');
    }

    public function base()
    {
        return $this->belongsTo(Base::class, 'base_id');
    }
}
