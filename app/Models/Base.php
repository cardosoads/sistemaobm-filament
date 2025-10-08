<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Modelo para representar as bases operacionais do sistema.
 * 
 * @property int $id
 * @property string $uf Estado (UF) da base
 * @property string $base Nome da base (cidade)
 * @property string $regional Região geográfica da base
 * @property string $sigla Sigla identificadora da base
 * @property bool $status Status ativo/inativo da base
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Base extends Model
{
    use HasFactory;

    /**
     * Nome da tabela no banco de dados.
     */
    protected $table = 'bases';

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'uf',
        'base',
        'regional',
        'sigla',
        'status',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     */
    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Atributos que devem ser ocultados na serialização.
     */
    protected $hidden = [];

    /**
     * Valores padrão para os atributos.
     */
    protected $attributes = [
        'status' => true,
    ];

    /**
     * Mapeamento de UFs para suas respectivas regionais.
     */
    public const REGIONAIS = [
        // Norte
        'AC' => 'Norte',
        'AP' => 'Norte', 
        'AM' => 'Norte',
        'PA' => 'Norte',
        'RO' => 'Norte',
        'RR' => 'Norte',
        'TO' => 'Norte',
        
        // Nordeste
        'AL' => 'Nordeste',
        'BA' => 'Nordeste',
        'CE' => 'Nordeste',
        'MA' => 'Nordeste',
        'PB' => 'Nordeste',
        'PE' => 'Nordeste',
        'PI' => 'Nordeste',
        'RN' => 'Nordeste',
        'SE' => 'Nordeste',
        
        // Centro-Oeste
        'GO' => 'Centro-Oeste',
        'MT' => 'Centro-Oeste',
        'MS' => 'Centro-Oeste',
        'DF' => 'Centro-Oeste',
        
        // Sudeste
        'ES' => 'Sudeste',
        'MG' => 'Sudeste',
        'RJ' => 'Sudeste',
        'SP' => 'Sudeste',
        
        // Sul
        'PR' => 'Sul',
        'RS' => 'Sul',
        'SC' => 'Sul',
    ];

    /**
     * Lista de UFs válidas.
     */
    public const UFS_VALIDAS = [
        'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
        'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
        'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
    ];

    /**
     * Boot do modelo para configurar eventos.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Automaticamente define a regional baseada na UF
        static::saving(function (Base $base) {
            if ($base->isDirty('uf') && $base->uf) {
                $base->regional = self::getRegionalByUf($base->uf);
            }
        });

        // Normaliza a sigla para maiúscula
        static::saving(function (Base $base) {
            if ($base->isDirty('sigla') && $base->sigla) {
                $base->sigla = Str::upper($base->sigla);
            }
        });

        // Normaliza a UF para maiúscula
        static::saving(function (Base $base) {
            if ($base->isDirty('uf') && $base->uf) {
                $base->uf = Str::upper($base->uf);
            }
        });
    }

    /**
     * Obtém a regional baseada na UF fornecida.
     */
    public static function getRegionalByUf(string $uf): string
    {
        return self::REGIONAIS[Str::upper($uf)] ?? 'Não definida';
    }

    /**
     * Obtém todas as regionais disponíveis.
     */
    public static function getRegionaisDisponiveis(): array
    {
        return array_unique(array_values(self::REGIONAIS));
    }

    /**
     * Obtém as opções de UF formatadas para select.
     */
    public static function getUfOptions(): array
    {
        $estados = [
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AP' => 'Amapá',
            'AM' => 'Amazonas',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MT' => 'Mato Grosso',
            'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais',
            'PA' => 'Pará',
            'PB' => 'Paraíba',
            'PR' => 'Paraná',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'SC' => 'Santa Catarina',
            'SP' => 'São Paulo',
            'SE' => 'Sergipe',
            'TO' => 'Tocantins',
        ];

        return $estados;
    }

    /**
     * Accessor para formatar o nome da base.
     */
    public function getBaseFormatadaAttribute(): string
    {
        return Str::title($this->base);
    }

    /**
     * Accessor para obter o nome completo da UF.
     */
    public function getUfNomeCompletoAttribute(): string
    {
        $estados = self::getUfOptions();
        return $estados[$this->uf] ?? $this->uf;
    }

    /**
     * Accessor para verificar se a base está ativa.
     */
    public function getAtivaAttribute(): bool
    {
        return (bool) $this->status;
    }

    /**
     * Mutator para normalizar o nome da base.
     */
    public function setBaseAttribute(string $value): void
    {
        $this->attributes['base'] = Str::title(trim($value));
    }

    /**
     * Mutator para normalizar a UF.
     */
    public function setUfAttribute(string $value): void
    {
        $this->attributes['uf'] = Str::upper(trim($value));
    }

    /**
     * Mutator para normalizar a sigla.
     */
    public function setSiglaAttribute(string $value): void
    {
        $this->attributes['sigla'] = Str::upper(trim($value));
    }

    /**
     * Scope para filtrar apenas bases ativas.
     */
    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope para filtrar apenas bases inativas.
     */
    public function scopeInativas(Builder $query): Builder
    {
        return $query->where('status', false);
    }

    /**
     * Scope para filtrar por UF.
     */
    public function scopePorUf(Builder $query, string $uf): Builder
    {
        return $query->where('uf', Str::upper($uf));
    }

    /**
     * Scope para filtrar por regional.
     */
    public function scopePorRegional(Builder $query, string $regional): Builder
    {
        return $query->where('regional', $regional);
    }

    /**
     * Scope para buscar por termo (base, sigla ou UF).
     */
    public function scopeBuscar(Builder $query, string $termo): Builder
    {
        $termo = Str::upper($termo);
        
        return $query->where(function (Builder $q) use ($termo) {
            $q->where('base', 'like', "%{$termo}%")
              ->orWhere('sigla', 'like', "%{$termo}%")
              ->orWhere('uf', 'like', "%{$termo}%");
        });
    }

    /**
     * Scope para ordenar por UF e depois por base.
     */
    public function scopeOrdenadoPorUfEBase(Builder $query): Builder
    {
        return $query->orderBy('uf')->orderBy('base');
    }

    /**
     * Relacionamento com recursos humanos.
     */
    public function recursosHumanos()
    {
        return $this->hasMany(RecursoHumano::class);
    }

    /**
     * Relacionamento com recursos humanos ativos.
     */
    public function recursosHumanosAtivos()
    {
        return $this->hasMany(RecursoHumano::class)->where('active', true);
    }

    /**
     * Verifica se a UF é válida.
     */
    public function isUfValida(): bool
    {
        return in_array($this->uf, self::UFS_VALIDAS);
    }

    /**
     * Verifica se a base está ativa.
     */
    public function isAtiva(): bool
    {
        return $this->status === true;
    }

    /**
     * Ativa a base.
     */
    public function ativar(): bool
    {
        return $this->update(['status' => true]);
    }

    /**
     * Desativa a base.
     */
    public function desativar(): bool
    {
        return $this->update(['status' => false]);
    }

    /**
     * Alterna o status da base.
     */
    public function alternarStatus(): bool
    {
        return $this->update(['status' => !$this->status]);
    }

    /**
     * Representação em string do modelo.
     */
    public function __toString(): string
    {
        return "{$this->sigla} - {$this->base} ({$this->uf})";
    }

    /**
     * Configuração para serialização JSON.
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        
        // Adiciona campos computados
        $array['base_formatada'] = $this->base_formatada;
        $array['uf_nome_completo'] = $this->uf_nome_completo;
        $array['ativa'] = $this->ativa;
        
        return $array;
    }
}
