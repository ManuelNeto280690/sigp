<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class EmbarcacaoConcessionaria extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'embarcacao_concessionaria';

    protected $fillable = [
        'concessionaria_id',
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
        'arqueacao_bruta' => 'integer',
        'arqueacao_liquida' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'esperado',
        'is_active' => true
    ];

    // Relacionamentos
    public function concessionaria(): BelongsTo
    {
        return $this->belongsTo(Concessionaria::class);
    }

    public function entradaSaidas(): HasMany
    {
        return $this->hasMany(EntradaSaidaEmbarcacaoConcessionaria::class, 'embarcacao_id');
    }

    // Scopes
    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePorStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeEsperados(Builder $query): Builder
    {
        return $query->where('status', 'esperado');
    }

    public function scopeAtracados(Builder $query): Builder
    {
        return $query->where('status', 'atracado');
    }

    public function scopeOperando(Builder $query): Builder
    {
        return $query->where('status', 'operando');
    }

    public function scopePartidos(Builder $query): Builder
    {
        return $query->where('status', 'partido');
    }

    public function scopePorTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo_embarcacao', $tipo);
    }

    public function scopePorBandeira(Builder $query, string $bandeira): Builder
    {
        return $query->where('bandeira', $bandeira);
    }

    public function scopePorConcessionaria(Builder $query, string $concessionariaId): Builder
    {
        return $query->where('concessionaria_id', $concessionariaId);
    }

    public function scopeComETA(Builder $query, Carbon $inicio = null, Carbon $fim = null): Builder
    {
        $query = $query->whereNotNull('eta');
        
        if ($inicio && $fim) {
            $query->whereBetween('eta', [$inicio, $fim]);
        }
        
        return $query;
    }

    public function scopeComETD(Builder $query, Carbon $inicio = null, Carbon $fim = null): Builder
    {
        $query = $query->whereNotNull('etd');
        
        if ($inicio && $fim) {
            $query->whereBetween('etd', [$inicio, $fim]);
        }
        
        return $query;
    }

    public function scopeAtrasados(Builder $query): Builder
    {
        return $query->where('eta', '<', now())
                    ->whereIn('status', ['esperado']);
    }

    public function scopeProximasChegadas(Builder $query, int $horas = 24): Builder
    {
        return $query->whereBetween('eta', [
            now(),
            now()->addHours($horas)
        ])->where('status', 'esperado');
    }

    public function scopeProximasPartidas(Builder $query, int $horas = 24): Builder
    {
        return $query->whereBetween('etd', [
            now(),
            now()->addHours($horas)
        ])->whereIn('status', ['atracado', 'operando']);
    }

    // Métodos auxiliares
    public function isEsperado(): bool
    {
        return $this->status === 'esperado';
    }

    public function isAtracado(): bool
    {
        return $this->status === 'atracado';
    }

    public function isOperando(): bool
    {
        return $this->status === 'operando';
    }

    public function isPartido(): bool
    {
        return $this->status === 'partido';
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'esperado' => 'bg-blue-100 text-blue-800',
            'atracado' => 'bg-green-100 text-green-800',
            'operando' => 'bg-yellow-100 text-yellow-800',
            'partido' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'esperado' => 'Esperado',
            'atracado' => 'Atracado',
            'operando' => 'Operando',
            'partido' => 'Partido',
            default => 'Desconhecido'
        };
    }

    public function getTipoEmbarcacaoLabel(): string
    {
        return match($this->tipo_embarcacao) {
            'container' => 'Porta-contêiner',
            'graneis_solidos' => 'Granéis Sólidos',
            'graneis_liquidos' => 'Granéis Líquidos',
            'carga_geral' => 'Carga Geral',
            'passageiros' => 'Passageiros',
            'tanque' => 'Navio Tanque',
            'ro_ro' => 'Ro-Ro',
            'frigorífico' => 'Frigorífico',
            default => ucfirst(str_replace('_', ' ', $this->tipo_embarcacao))
        };
    }

    public function getDimensoesFormatadas(): string
    {
        $dimensoes = [];
        
        if ($this->comprimento) {
            $dimensoes[] = "C: {$this->comprimento}m";
        }
        
        if ($this->largura) {
            $dimensoes[] = "L: {$this->largura}m";
        }
        
        if ($this->calado) {
            $dimensoes[] = "Cal: {$this->calado}m";
        }
        
        return implode(' | ', $dimensoes);
    }

    public function getArqueacaoFormatada(): string
    {
        $arqueacao = [];
        
        if ($this->arqueacao_bruta) {
            $arqueacao[] = "AB: {$this->arqueacao_bruta}";
        }
        
        if ($this->arqueacao_liquida) {
            $arqueacao[] = "AL: {$this->arqueacao_liquida}";
        }
        
        return implode(' | ', $arqueacao);
    }

    public function getTempoEstadia(): ?int
    {
        if (!$this->ata || !$this->atd) {
            return null;
        }
        
        return $this->ata->diffInHours($this->atd);
    }

    public function getTempoEsperaAtracacao(): ?int
    {
        if (!$this->eta || !$this->ata) {
            return null;
        }
        
        return $this->eta->diffInHours($this->ata);
    }

    public function isAtrasado(): bool
    {
        if ($this->isPartido()) {
            return false;
        }
        
        if ($this->isEsperado() && $this->eta) {
            return $this->eta->isPast();
        }
        
        if (($this->isAtracado() || $this->isOperando()) && $this->etd) {
            return $this->etd->isPast();
        }
        
        return false;
    }

    public function getAtrasoMinutos(): ?int
    {
        if (!$this->isAtrasado()) {
            return null;
        }
        
        if ($this->isEsperado() && $this->eta) {
            return $this->eta->diffInMinutes(now());
        }
        
        if (($this->isAtracado() || $this->isOperando()) && $this->etd) {
            return $this->etd->diffInMinutes(now());
        }
        
        return null;
    }

    // Métodos de ação
    public function atracar(): bool
    {
        if (!$this->isEsperado()) {
            return false;
        }
        
        $this->update([
            'status' => 'atracado',
            'ata' => now()
        ]);
        
        return true;
    }

    public function iniciarOperacao(): bool
    {
        if (!$this->isAtracado()) {
            return false;
        }
        
        $this->update([
            'status' => 'operando'
        ]);
        
        return true;
    }

    public function partir(): bool
    {
        if (!in_array($this->status, ['atracado', 'operando'])) {
            return false;
        }
        
        $this->update([
            'status' => 'partido',
            'atd' => now()
        ]);
        
        return true;
    }

    public function atualizarETA(Carbon $novaETA, string $motivo = null): bool
    {
        $this->update([
            'eta' => $novaETA,
            'observacoes' => $motivo ? 
                ($this->observacoes ? $this->observacoes . ' | ' . $motivo : $motivo) : 
                $this->observacoes
        ]);
        
        return true;
    }

    public function atualizarETD(Carbon $novaETD, string $motivo = null): bool
    {
        $this->update([
            'etd' => $novaETD,
            'observacoes' => $motivo ? 
                ($this->observacoes ? $this->observacoes . ' | ' . $motivo : $motivo) : 
                $this->observacoes
        ]);
        
        return true;
    }

    // Validações
    public static function rules($id = null): array
    {
        return [
            'concessionaria_id' => 'required|uuid|exists:concessionarias,id',
            'nome' => 'required|string|max:255',
            'imo' => [
                'required',
                'string',
                'size:7',
                'regex:/^[0-9]{7}$/',
                Rule::unique('embarcacao_concessionaria')->ignore($id)
            ],
            'mmsi' => [
                'required',
                'string',
                'size:9',
                'regex:/^[0-9]{9}$/',
                Rule::unique('embarcacao_concessionaria')->ignore($id)
            ],
            'bandeira' => 'required|string|max:100',
            'tipo_embarcacao' => 'required|string|max:100',
            'comprimento' => 'nullable|numeric|min:0|max:999.99',
            'largura' => 'nullable|numeric|min:0|max:999.99',
            'calado' => 'nullable|numeric|min:0|max:99.99',
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
            'observacoes' => 'nullable|string|max:1000'
        ];
    }

    public static function messages(): array
    {
        return [
            'concessionaria_id.required' => 'A concessionária é obrigatória.',
            'concessionaria_id.exists' => 'A concessionária selecionada não existe.',
            'nome.required' => 'O nome da embarcação é obrigatório.',
            'imo.required' => 'O número IMO é obrigatório.',
            'imo.size' => 'O número IMO deve ter exatamente 7 dígitos.',
            'imo.regex' => 'O número IMO deve conter apenas números.',
            'imo.unique' => 'Este número IMO já está cadastrado.',
            'mmsi.required' => 'O número MMSI é obrigatório.',
            'mmsi.size' => 'O número MMSI deve ter exatamente 9 dígitos.',
            'mmsi.regex' => 'O número MMSI deve conter apenas números.',
            'mmsi.unique' => 'Este número MMSI já está cadastrado.',
            'bandeira.required' => 'A bandeira é obrigatória.',
            'tipo_embarcacao.required' => 'O tipo de embarcação é obrigatório.',
            'etd.after_or_equal' => 'A data de partida estimada deve ser posterior à chegada.',
            'atd.after_or_equal' => 'A data de partida real deve ser posterior à chegada.',
            'status.in' => 'Status inválido.'
        ];
    }

    // Métodos estáticos
    public static function proximasChegadas(int $limite = 10)
    {
        return static::with('concessionaria')
            ->where('status', 'esperado')
            ->whereNotNull('eta')
            ->orderBy('eta')
            ->limit($limite)
            ->get();
    }

    public static function proximasPartidas(int $limite = 10)
    {
        return static::with('concessionaria')
            ->whereIn('status', ['atracado', 'operando'])
            ->whereNotNull('etd')
            ->orderBy('etd')
            ->limit($limite)
            ->get();
    }

    public static function embarcacoesAtracadas()
    {
        return static::with('concessionaria')
            ->whereIn('status', ['atracado', 'operando'])
            ->orderBy('ata')
            ->get();
    }

    public static function estatisticas(Carbon $inicio = null, Carbon $fim = null)
    {
        $query = static::query();
        
        if ($inicio && $fim) {
            $query->whereBetween('created_at', [$inicio, $fim]);
        }
        
        return [
            'total' => $query->count(),
            'esperados' => $query->clone()->where('status', 'esperado')->count(),
            'atracados' => $query->clone()->where('status', 'atracado')->count(),
            'operando' => $query->clone()->where('status', 'operando')->count(),
            'partidos' => $query->clone()->where('status', 'partido')->count(),
            'atrasados' => static::atrasados()->count(),
            'tempo_medio_estadia' => static::whereNotNull('ata')
                ->whereNotNull('atd')
                ->get()
                ->avg(function ($embarcacao) {
                    return $embarcacao->getTempoEstadia();
                })
        ];
    }

    public static function relatorioPorTipo(Carbon $inicio = null, Carbon $fim = null)
    {
        $query = static::query();
        
        if ($inicio && $fim) {
            $query->whereBetween('created_at', [$inicio, $fim]);
        }
        
        return $query->selectRaw('tipo_embarcacao, COUNT(*) as total')
            ->groupBy('tipo_embarcacao')
            ->orderBy('total', 'desc')
            ->get();
    }

    public static function relatorioPorBandeira(Carbon $inicio = null, Carbon $fim = null)
    {
        $query = static::query();
        
        if ($inicio && $fim) {
            $query->whereBetween('created_at', [$inicio, $fim]);
        }
        
        return $query->selectRaw('bandeira, COUNT(*) as total')
            ->groupBy('bandeira')
            ->orderBy('total', 'desc')
            ->get();
    }
}