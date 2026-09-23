<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Concessionaria extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'concessionarias';

    protected $fillable = [
        'nome',
        'user_id',
        'nif',
        'email',
        'telefone',
        'endereco',
        'responsavel_nome',
        'responsavel_email',
        'responsavel_telefone',
        'data_inicio_concessao',
        'data_fim_concessao',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'data_inicio_concessao' => 'date',
        'data_fim_concessao' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $attributes = [
        'is_active' => true
    ];

    // Relacionamentos
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the users that belong to this concessionaria
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'concessionaria_id');
    }

    /**
     * Get the main user associated with this concessionaria
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function embarcacoes(): HasMany
    {
        return $this->hasMany(EmbarcacaoConcessionaria::class);
    }

   /* public function embarcacoes(): BelongsToMany
    {
        return $this->belongsToMany(Embarcacao::class, 'embarcacao_concessionaria')
                    ->withTimestamps();
    }*/

    public function terminais(): HasMany
    {
        return $this->hasMany(Terminal::class);
    }

    public function declaracoes(): HasMany
    {
        return $this->hasMany(Declaracao::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function movimentosTerminais(): HasMany
    {
        return $this->hasMany(MovimentoTerminal::class);
    }

    public function movimentosProdutos(): HasMany
    {
        return $this->hasMany(MovimentoProduto::class);
    }

    // Scopes
    public function scopeAtivo($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAtivas($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeConcessaoValida($query)
    {
        $hoje = now()->toDateString();
        return $query->where('data_inicio_concessao', '<=', $hoje)
                    ->where(function($q) use ($hoje) {
                        $q->whereNull('data_fim_concessao')
                          ->orWhere('data_fim_concessao', '>=', $hoje);
                    });
    }

    public function scopeConcessaoExpirada($query)
    {
        return $query->where('data_fim_concessao', '<', now()->toDateString());
    }

    public function scopePorNome($query, $nome)
    {
        return $query->where('nome', 'like', '%' . $nome . '%');
    }

    public function scopePorNif($query, $nif)
    {
        return $query->where('nif', $nif);
    }

    // Métodos auxiliares
    public function isConcessaoValida(): bool
    {
        $hoje = now()->toDateString();
        
        if ($this->data_inicio_concessao > $hoje) {
            return false;
        }
        
        if ($this->data_fim_concessao && $this->data_fim_concessao < $hoje) {
            return false;
        }
        
        return true;
    }

    public function isConcessaoExpirada(): bool
    {
        return $this->data_fim_concessao && $this->data_fim_concessao < now()->toDateString();
    }

    public function getDiasRestantesConcessaoAttribute(): ?int
    {
        if (!$this->data_fim_concessao) {
            return null;
        }
        
        return now()->diffInDays($this->data_fim_concessao, false);
    }

    public function getStatusConcessaoAttribute(): string
    {
        if (!$this->isConcessaoValida()) {
            return $this->isConcessaoExpirada() ? 'expirada' : 'pendente';
        }
        
        $diasRestantes = $this->dias_restantes_concessao;
        
        if ($diasRestantes === null) {
            return 'indefinida';
        }
        
        if ($diasRestantes <= 30) {
            return 'expirando';
        }
        
        return 'ativa';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status_concessao) {
            'ativa' => 'green',
            'expirando' => 'yellow',
            'expirada' => 'red',
            'pendente' => 'blue',
            'indefinida' => 'gray',
            default => 'gray'
        };
    }

    public function getTotalEmbarcacoesAttribute(): int
    {
        return $this->embarcacoes()->count();
    }

    public function getTotalTerminaisAttribute(): int
    {
        return $this->terminais()->count();
    }

    // Validações
    public static function rules($id = null): array
    {
        return [
            'nome' => 'required|string|max:255',
            'nif' => 'required|string|unique:concessionarias,nif,' . $id,
            'email' => 'required|email|unique:concessionarias,email,' . $id,
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string',
            'responsavel_nome' => 'nullable|string|max:255',
            'responsavel_email' => 'nullable|email|max:255',
            'responsavel_telefone' => 'nullable|string|max:20',
            'data_inicio_concessao' => 'nullable|date',
            'data_fim_concessao' => 'nullable|date|after_or_equal:data_inicio_concessao',
            'is_active' => 'boolean',
            'created_by' => 'required|exists:users,id'
        ];
    }

    public static function messages(): array
    {
        return [
            'nome.required' => 'O nome da concessionária é obrigatório.',
            'nif.required' => 'O NIF é obrigatório.',
            'nif.unique' => 'Este NIF já está cadastrado.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.unique' => 'Este email já está cadastrado.',
            'responsavel_email.email' => 'O email do responsável deve ter um formato válido.',
            'data_fim_concessao.after_or_equal' => 'A data de fim da concessão deve ser posterior à data de início.',
            'created_by.required' => 'O usuário criador é obrigatório.',
            'created_by.exists' => 'O usuário criador não existe.'
        ];
    }
}