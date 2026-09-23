<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Declaracao extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'declaracoes';

    protected $fillable = [
        'embarcacao_id',
        'user_id',
        'numero_protocolo',
        'tipo',
        'dados_declaracao',
        'status',
        'observacoes',
        'motivo_rejeicao',
        'aprovado_por',
        'aprovado_em',
        'data_vencimento',
        'is_active'
    ];

    protected $casts = [
        'dados_declaracao' => 'array',
        'aprovado_em' => 'datetime',
        'data_vencimento' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'pendente',
        'is_active' => true
    ];

    // Relacionamentos
    public function embarcacao(): BelongsTo
    {
        return $this->belongsTo(Embarcacao::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

        public function entradasSaidas(): HasMany
    {
        return $this->hasMany(EntradaSaidaEmbarcacao::class);
    }

    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por');
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

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeEmAnalise($query)
    {
        return $query->where('status', 'em_analise');
    }

    public function scopeAprovadas($query)
    {
        return $query->where('status', 'aprovada');
    }

    public function scopeRejeitadas($query)
    {
        return $query->where('status', 'rejeitada');
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorEmbarcacao($query, $embarcacaoId)
    {
        return $query->where('embarcacao_id', $embarcacaoId);
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeVencidas($query)
    {
        return $query->whereNotNull('data_vencimento')
                    ->where('data_vencimento', '<', now());
    }

    public function scopeVencendoEm($query, $dias = 7)
    {
        return $query->whereNotNull('data_vencimento')
                    ->whereBetween('data_vencimento', [now(), now()->addDays($dias)]);
    }

    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('created_at', [$dataInicio, $dataFim]);
    }

    // Métodos auxiliares
    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function isEmAnalise(): bool
    {
        return $this->status === 'em_analise';
    }

    public function isAprovada(): bool
    {
        return $this->status === 'aprovada';
    }

    public function isRejeitada(): bool
    {
        return $this->status === 'rejeitada';
    }

    public function isVencida(): bool
    {
        return $this->data_vencimento && $this->data_vencimento->isPast();
    }

    public function isVencendoEm($dias = 7): bool
    {
        if (!$this->data_vencimento) {
            return false;
        }
        
        return $this->data_vencimento->isBetween(now(), now()->addDays($dias));
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->isVencida()) {
            return 'red';
        }
        
        return match($this->status) {
            'pendente' => 'yellow',
            'em_analise' => 'blue',
            'aprovada' => 'green',
            'rejeitada' => 'red',
            default => 'gray'
        };
    }

    public function getTipoDisplayAttribute(): string
    {
        return match($this->tipo) {
            'chegada' => 'Chegada',
            'saida' => 'Saída',
            'movimentacao_carga' => 'Movimentação de Carga',
            'abastecimento' => 'Abastecimento',
            'reparos' => 'Reparos',
            default => ucfirst($this->tipo)
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'pendente' => 'Pendente',
            'em_analise' => 'Em Análise',
            'aprovada' => 'Aprovada',
            'rejeitada' => 'Rejeitada',
            default => ucfirst($this->status)
        };
    }

    public function getDiasParaVencimentoAttribute(): ?int
    {
        if (!$this->data_vencimento) {
            return null;
        }
        
        return now()->diffInDays($this->data_vencimento, false);
    }

    public function getTempoProcessamentoAttribute(): ?string
    {
        if (!$this->aprovado_em) {
            return null;
        }
        
        return $this->created_at->diffForHumans($this->aprovado_em, true);
    }

    // Métodos de ação
    public function aprovar(User $usuario, ?string $observacoes = null): bool
    {
        if (!$this->isPendente() && !$this->isEmAnalise()) {
            return false;
        }
        
        $this->update([
            'status' => 'aprovada',
            'aprovado_por' => $usuario->id,
            'aprovado_em' => now(),
            'observacoes' => $observacoes ?? $this->observacoes
        ]);
        
        return true;
    }

    public function rejeitar(User $usuario, string $motivo, ?string $observacoes = null): bool
    {
        if (!$this->isPendente() && !$this->isEmAnalise()) {
            return false;
        }
        
        $this->update([
            'status' => 'rejeitada',
            'motivo_rejeicao' => $motivo,
            'aprovado_por' => $usuario->id,
            'aprovado_em' => now(),
            'observacoes' => $observacoes ?? $this->observacoes
        ]);
        
        return true;
    }

    public function colocarEmAnalise(): bool
    {
        if (!$this->isPendente()) {
            return false;
        }
        
        $this->update(['status' => 'em_analise']);
        
        return true;
    }

    public function getDadoDeclaracao($chave, $default = null)
    {
        return data_get($this->dados_declaracao, $chave, $default);
    }

    public function setDadoDeclaracao($chave, $valor): void
    {
        $dados = $this->dados_declaracao ?? [];
        data_set($dados, $chave, $valor);
        $this->dados_declaracao = $dados;
        $this->save();
    }

    // Validações
    public static function rules($id = null): array
    {
        return [
            'embarcacao_id' => 'required|exists:embarcacoes,id',
            'user_id' => 'required|exists:users,id',
            'numero_protocolo' => 'required|string|unique:declaracoes,numero_protocolo,' . $id,
            'tipo' => 'required|in:chegada,saida,movimentacao_carga,abastecimento,reparos',
            'dados_declaracao' => 'required|array',
            'status' => 'required|in:pendente,em_analise,aprovada,rejeitada',
            'observacoes' => 'nullable|string',
            'motivo_rejeicao' => 'nullable|string|required_if:status,rejeitada',
            'aprovado_por' => 'nullable|exists:users,id',
            'aprovado_em' => 'nullable|date',
            'data_vencimento' => 'nullable|date|after:now',
            'is_active' => 'boolean'
        ];
    }

    public static function messages(): array
    {
        return [
            'embarcacao_id.required' => 'A embarcação é obrigatória.',
            'embarcacao_id.exists' => 'A embarcação selecionada não existe.',
            'user_id.required' => 'O usuário é obrigatório.',
            'user_id.exists' => 'O usuário selecionado não existe.',
            'numero_protocolo.required' => 'O número do protocolo é obrigatório.',
            'numero_protocolo.unique' => 'Este número de protocolo já existe.',
            'tipo.required' => 'O tipo da declaração é obrigatório.',
            'tipo.in' => 'Tipo inválido. Use: chegada, saida, movimentacao_carga, abastecimento ou reparos.',
            'dados_declaracao.required' => 'Os dados da declaração são obrigatórios.',
            'dados_declaracao.array' => 'Os dados da declaração devem ser um array.',
            'status.in' => 'Status inválido. Use: pendente, em_analise, aprovada ou rejeitada.',
            'motivo_rejeicao.required_if' => 'O motivo da rejeição é obrigatório quando o status for rejeitada.',
            'data_vencimento.after' => 'A data de vencimento deve ser futura.',
            'aprovado_por.exists' => 'O usuário aprovador não existe.'
        ];
    }

    // Geração automática de número de protocolo
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($declaracao) {
            if (!$declaracao->numero_protocolo) {
                $declaracao->numero_protocolo = static::gerarNumeroProtocolo();
            }
        });
    }

    public static function gerarNumeroProtocolo(): string
    {
        $ano = date('Y');
        $mes = date('m');
        
        $ultimo = static::whereYear('created_at', $ano)
                       ->whereMonth('created_at', $mes)
                       ->orderBy('created_at', 'desc')
                       ->first();
        
        $sequencial = $ultimo ? (int) substr($ultimo->numero_protocolo, -4) + 1 : 1;
        
        return sprintf('DECL-%s%s-%04d', $ano, $mes, $sequencial);
    }
}