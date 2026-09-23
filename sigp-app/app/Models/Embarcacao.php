<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Embarcacao extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    // Constante para tipos de embarcação
    public const TIPOS_EMBARCACAO = [
        'container',
        'graneis_solidos',
        'graneis_liquidos',
        'carga_geral',
        'passageiros',
        'tanque',
        'ro_ro',
        'frigorifico'
    ];

    protected $table = 'embarcacoes';

    protected $fillable = [
        'nome',
        'imo',
        'mmsi',
        'bandeira',
        'tipo_embarcacao',
        'comprimento',
        'largura',
        'calado',
        'arqueacao_bruta',
        'arqueacao_liquida',
        'armador',
        'agente_maritimo',
        'capitao',
        'porto_origem',
        'porto_destino',
        'eta',
        'etd',
        'ata',
        'atd',
        'status',
        'observacoes',
        'is_active'
    ];

    protected $casts = [
        'eta' => 'datetime',
        'etd' => 'datetime',
        'ata' => 'datetime',
        'atd' => 'datetime',
        'comprimento' => 'decimal:2',
        'largura' => 'decimal:2',
        'calado' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'esperado',
        'is_active' => true
    ];

    // Relacionamentos
    public function concessionarias(): BelongsToMany
    {
        return $this->belongsToMany(Concessionaria::class, 'embarcacao_concessionaria')
                    ->withTimestamps();
    }

    public function entradasSaidas(): HasMany
    {
        return $this->hasMany(EntradaSaidaEmbarcacao::class);
    }

    public function declaracoes(): HasMany
    {
        return $this->hasMany(Declaracao::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class);
    }

    public function incidentes(): HasMany
    {
        return $this->hasMany(Incidente::class);
    }

    public function inspecoes(): HasMany
    {
        return $this->hasMany(Inspecao::class);
    }

    // Scopes
    public function scopeAtivo($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeEsperadas($query)
    {
        return $query->where('status', 'esperado');
    }

    public function scopeAtracadas($query)
    {
        return $query->where('status', 'atracado');
    }

    public function scopeOperando($query)
    {
        return $query->where('status', 'operando');
    }

    public function scopePartidas($query)
    {
        return $query->where('status', 'partido');
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_embarcacao', $tipo);
    }

    public function scopePorBandeira($query, $bandeira)
    {
        return $query->where('bandeira', $bandeira);
    }

    // Métodos auxiliares
    public function isAtracada(): bool
    {
        return $this->status === 'atracado';
    }

    public function isOperando(): bool
    {
        return $this->status === 'operando';
    }

    public function isEsperada(): bool
    {
        return $this->status === 'esperado';
    }

    public function isPartida(): bool
    {
        return $this->status === 'partido';
    }

    public function getTempoEstadiaAttribute(): ?int
    {
        if ($this->ata && $this->atd) {
            return Carbon::parse($this->ata)->diffInHours(Carbon::parse($this->atd));
        }
        
        if ($this->ata && !$this->atd) {
            return Carbon::parse($this->ata)->diffInHours(now());
        }
        
        return null;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'esperado' => 'yellow',
            'atracado' => 'blue',
            'operando' => 'green',
            'partido' => 'gray',
            default => 'gray'
        };
    }

    // Validações
    public static function rules($id = null): array
    {
        return [
            'nome' => 'required|string|max:255',
            'imo' => 'required|string|unique:embarcacoes,imo,' . $id,
            'mmsi' => 'required|string|unique:embarcacoes,mmsi,' . $id,
            'bandeira' => 'required|string|max:255',
            'tipo_embarcacao' => 'required|string|max:255',
            'comprimento' => 'nullable|numeric|min:0|max:999999.99',
            'largura' => 'nullable|numeric|min:0|max:999999.99',
            'calado' => 'nullable|numeric|min:0|max:999999.99',
            'arqueacao_bruta' => 'nullable|integer|min:0',
            'arqueacao_liquida' => 'nullable|integer|min:0',
            'armador' => 'nullable|string|max:255',
            'agente_maritimo' => 'nullable|string|max:255',
            'capitao' => 'nullable|string|max:255',
            'porto_origem' => 'nullable|string|max:255',
            'porto_destino' => 'nullable|string|max:255',
            'eta' => 'nullable|date',
            'etd' => 'nullable|date|after_or_equal:eta',
            'ata' => 'nullable|date',
            'atd' => 'nullable|date|after_or_equal:ata',
            'status' => 'required|in:esperado,atracado,operando,partido',
            'observacoes' => 'nullable|string',
            'is_active' => 'boolean'
        ];
    }

    public static function messages(): array
    {
        return [
            'nome.required' => 'O nome da embarcação é obrigatório.',
            'imo.required' => 'O número IMO é obrigatório.',
            'imo.unique' => 'Este número IMO já está cadastrado.',
            'mmsi.required' => 'O número MMSI é obrigatório.',
            'mmsi.unique' => 'Este número MMSI já está cadastrado.',
            'bandeira.required' => 'A bandeira é obrigatória.',
            'tipo_embarcacao.required' => 'O tipo de embarcação é obrigatório.',
            'etd.after_or_equal' => 'A data de partida estimada deve ser posterior à chegada estimada.',
            'atd.after_or_equal' => 'A data de partida real deve ser posterior à chegada real.',
            'status.in' => 'Status inválido. Use: esperado, atracado, operando ou partido.'
        ];
    }
}