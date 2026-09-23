<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Terminal extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'terminais';

    protected $fillable = [
        'concessionaria_id',
        'nome',
        'codigo',
        'tipo',
        'descricao',
        'area_total',
        'area_operacional',
        'numero_bercos',
        'calado_maximo',
        'capacidade_armazenagem',
        'equipamentos',
        'status',
        'observacoes',
        'is_active'
    ];

    protected $casts = [
        'area_total' => 'decimal:2',
        'area_operacional' => 'decimal:2',
        'calado_maximo' => 'decimal:2',
        'equipamentos' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'ativo',
        'numero_bercos' => 1,
        'is_active' => true
    ];

    // Relacionamentos
    public function concessionaria(): BelongsTo
    {
        return $this->belongsTo(Concessionaria::class);
    }

    public function movimentosTerminais(): HasMany
    {
        return $this->hasMany(MovimentoTerminal::class);
    }

    public function movimentosProdutos(): HasMany
    {
        return $this->hasMany(MovimentoProduto::class);
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

    public function bercos(): HasMany
    {
        return $this->hasMany(Berco::class);
    }

    public function guindastes(): HasMany
    {
        return $this->hasMany(Guindaste::class);
    }

    // Boot method para geração automática de código
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($terminal) {
            if (empty($terminal->codigo)) {
                $terminal->codigo = static::gerarCodigo();
            }
        });
    }

    /**
     * Gera um código único para o terminal iniciando com TM
     */
    public static function gerarCodigo(): string
    {
        $ano = date('Y');
        $ultimoTerminal = static::whereYear('created_at', $ano)
                               ->where('codigo', 'like', 'TM' . $ano . '%')
                               ->orderBy('codigo', 'desc')
                               ->first();

        $sequencial = 1;
        if ($ultimoTerminal && $ultimoTerminal->codigo) {
            $ultimoNumero = (int) substr($ultimoTerminal->codigo, -4);
            $sequencial = $ultimoNumero + 1;
        }

        return 'TM' . $ano . str_pad($sequencial, 4, '0', STR_PAD_LEFT);
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

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopeInativos($query)
    {
        return $query->where('status', 'inativo');
    }

    public function scopeEmManutencao($query)
    {
        return $query->where('status', 'manutencao');
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorConcessionaria($query, $concessionariaId)
    {
        return $query->where('concessionaria_id', $concessionariaId);
    }

    public function scopeComCapacidade($query, $capacidadeMinima = null)
    {
        $query = $query->whereNotNull('capacidade_armazenagem');
        
        if ($capacidadeMinima) {
            $query->where('capacidade_armazenagem', '>=', $capacidadeMinima);
        }
        
        return $query;
    }

    // Métodos auxiliares
    public function isAtivo(): bool
    {
        return $this->status === 'ativo';
    }

    public function isInativo(): bool
    {
        return $this->status === 'inativo';
    }

    public function isEmManutencao(): bool
    {
        return $this->status === 'manutencao';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'ativo' => 'green',
            'inativo' => 'red',
            'manutencao' => 'yellow',
            default => 'gray'
        };
    }

    public function getTipoDisplayAttribute(): string
    {
        return match($this->tipo) {
            'carga geral' => 'carga geral',
            default => ucfirst($this->tipo)
        };
    }

    public function getPercentualOcupacaoAttribute(): ?float
    {
        if (!$this->area_total || !$this->area_operacional) {
            return null;
        }
        
        return ($this->area_operacional / $this->area_total) * 100;
    }

    public function getTotalMovimentosAttribute(): int
    {
        return $this->movimentosTerminais()->count();
    }

    public function getTotalMovimentosHojeAttribute(): int
    {
        return $this->movimentosTerminais()
                    ->whereDate('created_at', today())
                    ->count();
    }

    public function getCapacidadeDisponivelAttribute(): ?int
    {
        if (!$this->capacidade_armazenagem) {
            return null;
        }
        
        // Aqui você pode implementar lógica para calcular capacidade disponível
        // baseada nos movimentos atuais
        return $this->capacidade_armazenagem;
    }

    public function hasEquipamento($equipamento): bool
    {
        if (!$this->equipamentos) {
            return false;
        }
        
        return in_array($equipamento, $this->equipamentos);
    }

    public function addEquipamento($equipamento): void
    {
        $equipamentos = $this->equipamentos ?? [];
        
        if (!in_array($equipamento, $equipamentos)) {
            $equipamentos[] = $equipamento;
            $this->equipamentos = $equipamentos;
            $this->save();
        }
    }

    public function removeEquipamento($equipamento): void
    {
        if (!$this->equipamentos) {
            return;
        }
        
        $equipamentos = array_filter($this->equipamentos, function($item) use ($equipamento) {
            return $item !== $equipamento;
        });
        
        $this->equipamentos = array_values($equipamentos);
        $this->save();
    }

    // Validações
    public static function rules($id = null): array
    {
        return [
            'concessionaria_id' => 'required|exists:concessionarias,id',
            'nome' => 'required|string|max:255',
            'codigo' => 'required|string|unique:terminais,codigo,' . $id,
            'tipo' => 'required|in:container,graneis_solidos,graneis_liquidos,carga_geral,passageiros',
            'descricao' => 'nullable|string',
            'area_total' => 'nullable|numeric|min:0|max:99999999.99',
            'area_operacional' => 'nullable|numeric|min:0|max:99999999.99|lte:area_total',
            'numero_bercos' => 'required|integer|min:1|max:50',
            'calado_maximo' => 'nullable|numeric|min:0|max:999999.99',
            'capacidade_armazenagem' => 'nullable|integer|min:0',
            'equipamentos' => 'nullable|array',
            'equipamentos.*' => 'string|max:255',
            'status' => 'required|in:ativo,inativo,manutencao',
            'observacoes' => 'nullable|string',
            'is_active' => 'boolean'
        ];
    }

    public static function messages(): array
    {
        return [
            'concessionaria_id.required' => 'A concessionária é obrigatória.',
            'concessionaria_id.exists' => 'A concessionária selecionada não existe.',
            'nome.required' => 'O nome do terminal é obrigatório.',
            'codigo.required' => 'O código do terminal é obrigatório.',
            'codigo.unique' => 'Este código já está em uso.',
            'tipo.required' => 'O tipo do terminal é obrigatório.',
            'tipo.in' => 'Tipo inválido. Use: container, graneis_solidos, graneis_liquidos, carga_geral ou passageiros.',
            'area_operacional.lte' => 'A área operacional não pode ser maior que a área total.',
            'numero_bercos.required' => 'O número de berços é obrigatório.',
            'numero_bercos.min' => 'O terminal deve ter pelo menos 1 berço.',
            'numero_bercos.max' => 'O terminal não pode ter mais de 50 berços.',
            'status.in' => 'Status inválido. Use: ativo, inativo ou manutencao.'
        ];
    }
}