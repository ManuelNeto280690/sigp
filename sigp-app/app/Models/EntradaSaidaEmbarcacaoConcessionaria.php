<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class EntradaSaidaEmbarcacaoConcessionaria extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'entrada_saida_embarcacao_concessionarias';

    protected $fillable = [
        'embarcacao_id',
        'concessionaria_id',
        'terminal_id',
        'user_id',
        'numero_movimento',
        'tipo_movimento',
        'data_programada',
        'data_efetiva',
        'berco',
        'agente_maritimo',
        'capitania_origem',
        'capitania_destino',
        'motivo',
        'documentos_apresentados',
        'status',
        'observacoes',
        'autorizado_por',
        'autorizado_em',
        'is_active',
        'motivo_revogacao',
        'revogado_por',
        'revogado_em'
    ];

    protected $casts = [
        'data_programada' => 'datetime',
        'data_efetiva' => 'datetime',
        'autorizado_em' => 'datetime',
        'documentos_apresentados' => 'array',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
        'status' => 'programado'
    ];

    // Relacionamentos
    public function embarcacaoConcessionaria(): BelongsTo
    {
        return $this->belongsTo(EmbarcacaoConcessionaria::class, 'embarcacao_id');
    }

    public function concessionaria(): BelongsTo
    {
        return $this->belongsTo(Concessionaria::class, 'concessionaria_id');
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'terminal_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function autorizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autorizado_por');
    }

    public function facturas()
    {
        return $this->hasMany(\App\Models\Factura::class, 'entrada_saida_id');
    }

    // Scopes
    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePorConcessionaria(Builder $query, string $concessionariaId): Builder
    {
        return $query->where('concessionaria_id', $concessionariaId);
    }

    public function scopePorTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo_movimento', $tipo);
    }

    public function scopePorStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // Métodos auxiliares
    public function isProgramado(): bool
    {
        return $this->status === 'programado';
    }

    public function isAutorizado(): bool
    {
        return $this->status === 'autorizado';
    }

    public function isEmAndamento(): bool
    {
        return $this->status === 'em_andamento';
    }

    public function isConcluido(): bool
    {
        return $this->status === 'concluido';
    }

    public function isCancelado(): bool
    {
        return $this->status === 'cancelado';
    }

    public function isEntrada(): bool
    {
        return $this->tipo_movimento === 'entrada';
    }

    public function isSaida(): bool
    {
        return $this->tipo_movimento === 'saida';
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'programado' => 'bg-blue-100 text-blue-800',
            'autorizado' => 'bg-green-100 text-green-800',
            'em_andamento' => 'bg-yellow-100 text-yellow-800',
            'concluido' => 'bg-gray-100 text-gray-800',
            'cancelado' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'programado' => 'Programado',
            'autorizado' => 'Autorizado',
            'em_andamento' => 'Em Andamento',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
            default => 'Desconhecido'
        };
    }

    public function getTipoMovimentoLabel(): string
    {
        return match($this->tipo_movimento) {
            'entrada' => 'Entrada',
            'saida' => 'Saída',
            default => 'Desconhecido'
        };
    }

    public function getDuracaoEstimada(): ?int
    {
        if (!$this->data_programada || !$this->data_efetiva) {
            return null;
        }
        
        return $this->data_programada->diffInMinutes($this->data_efetiva);
    }

    // Métodos de workflow
    public function podeEditar(): bool
    {
        return in_array($this->status, ['programado', 'autorizado']);
    }

    public function podeAutorizar(): bool
    {
        return $this->status === 'programado';
    }

    public function podeIniciar(): bool
    {
        return $this->status === 'autorizado';
    }

    public function podeConcluir(): bool
    {
        return $this->status === 'em_andamento';
    }

    public function podeCancelar(): bool
    {
        return !in_array($this->status, ['concluido', 'cancelado']);
    }

    public function autorizar(): bool
    {
        if (!$this->podeAutorizar()) {
            return false;
        }
        
        $this->update([
            'status' => 'autorizado',
            'autorizado_por' => auth()->id(),
            'autorizado_em' => now()
        ]);
        
        return true;
    }

    public function iniciar(): bool
    {
        if (!$this->podeIniciar()) {
            return false;
        }
        
        $this->update([
            'status' => 'em_andamento',
            'data_efetiva' => now()
        ]);
        
        return true;
    }

    public function concluir(): bool
    {
        if (!$this->podeConcluir()) {
            return false;
        }
        
        $this->update([
            'status' => 'concluido'
        ]);
        
        return true;
    }

    public function cancelar(string $motivo = null): bool
    {
        if (!$this->podeCancelar()) {
            return false;
        }
        
        $this->update([
            'status' => 'cancelado',
            'observacoes' => $motivo ? ($this->observacoes ? $this->observacoes . "\n\nMotivo do cancelamento: " . $motivo : "Motivo do cancelamento: " . $motivo) : $this->observacoes
        ]);
        
        return true;
    }

    public function podeExcluir(): bool
    {
        // Não pode excluir se estiver concluído
        if ($this->isConcluido()) {
            return false;
        }
        
        // Pode excluir se estiver programado, autorizado, em andamento ou cancelado
        return in_array($this->status, ['programado', 'autorizado', 'em_andamento', 'cancelado']);
    }

    // Métodos estáticos
    public static function getStatusOptions(): array
    {
        return [
            'programado' => 'Programado',
            'autorizado' => 'Autorizado',
            'em_andamento' => 'Em Andamento',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado'
        ];
    }

    public static function getTipoMovimentoOptions(): array
    {
        return [
            'entrada' => 'Entrada',
            'saida' => 'Saída'
        ];
    }

    /**
     * Revogar movimento concluído ou cancelado
     */
    public function revogar(string $motivo = null): bool
    {
        if (!$this->isConcluido() && !$this->isCancelado()) {
            return false;
        }
        
        $this->update([
            'status' => 'em_andamento',
            'data_conclusao' => null,
            'observacoes_conclusao' => null,
            'motivo_revogacao' => $motivo,
            'revogado_por' => auth()->id(),
            'revogado_em' => now()
        ]);
        
        return true;
    }

    /**
     * Verificar se pode ser revogado
     */
    public function podeRevogar(): bool
    {
        return ($this->isConcluido() || $this->isCancelado()) && auth()->user()->hasRole('admin');
    }
}