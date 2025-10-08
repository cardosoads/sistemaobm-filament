<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClienteFornecedor extends Model
{
    use HasFactory;

    protected $table = 'clientes_fornecedores';

    protected $fillable = [
        'is_cliente',
        'codigo_cliente_omie',
        'codigo_cliente_integracao',
        'razao_social',
        'nome_fantasia',
        'cnpj_cpf',
        'email',
        'homepage',
        'telefone1_ddd',
        'telefone1_numero',
        'telefone2_ddd',
        'telefone2_numero',
        'fax_ddd',
        'fax_numero',
        'endereco',
        'endereco_numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'inativo',
        'tags',
        'inscricao_estadual',
        'inscricao_municipal',
        'pessoa_fisica',
        'optante_simples_nacional',
        'contribuinte',
        'exterior',
        'importado_api',
        'avatar',
        'observacoes',
        'obs_detalhadas',
        'recomendacao_atraso',
        'dados_originais_api',
        'ultima_sincronizacao',
        'status_sincronizacao',
        'data_inclusao',
        'data_alteracao',
    ];

    protected $casts = [
        'is_cliente' => 'boolean',
        'tags' => 'array',
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

    // Scopes para filtrar clientes e fornecedores
    public function scopeClientes($query)
    {
        return $query->where('is_cliente', true);
    }

    public function scopeFornecedores($query)
    {
        return $query->where('is_cliente', false);
    }

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

    // Accessors
    public function getTelefoneCompletoAttribute()
    {
        if ($this->telefone1_ddd && $this->telefone1_numero) {
            return "({$this->telefone1_ddd}) {$this->telefone1_numero}";
        }
        return null;
    }

    public function getTelefone2CompletoAttribute()
    {
        if ($this->telefone2_ddd && $this->telefone2_numero) {
            return "({$this->telefone2_ddd}) {$this->telefone2_numero}";
        }
        return null;
    }

    public function getFaxCompletoAttribute()
    {
        if ($this->fax_ddd && $this->fax_numero) {
            return "({$this->fax_ddd}) {$this->fax_numero}";
        }
        return null;
    }

    public function getEnderecoCompletoAttribute()
    {
        $endereco = $this->endereco;
        if ($this->endereco_numero) {
            $endereco .= ", {$this->endereco_numero}";
        }
        if ($this->complemento) {
            $endereco .= " - {$this->complemento}";
        }
        if ($this->bairro) {
            $endereco .= ", {$this->bairro}";
        }
        if ($this->cidade && $this->estado) {
            $endereco .= " - {$this->cidade}/{$this->estado}";
        }
        if ($this->cep) {
            $endereco .= " - CEP: {$this->cep}";
        }
        return $endereco;
    }

    public function getTipoAttribute()
    {
        return $this->is_cliente ? 'Cliente' : 'Fornecedor';
    }

    public function getStatusAttribute()
    {
        return $this->inativo === 'N' ? 'Ativo' : 'Inativo';
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
}
