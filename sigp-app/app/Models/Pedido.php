<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Pedido extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pedidos';

    protected $fillable = [
        // Informações básicas do navio
        'nome_navio',
        'numero_imo',
        'indicativo_chamada',
        'viagem_numero',
        'bandeira_navio',
        
        // Datas e horários
        'data_chegada',
        'hora_chegada',
        'data_partida',
        'hora_partida',
        
        // Informações do agente
        'nome_agente',
        'contato_agente',
        
        // Tripulação e passageiros
        'numero_tripulantes',
        'numero_passageiros',
        
        // Observações
        'observacoes_operacao',
        
        // Arquivos/Documentos (JSON)
        'certificados_navio',
        'declaracoes_cargas',
        'declaracao_provisoes_bordo',
        'declaracao_pertences_tripulacao',
        'documentos_tripulantes',
        'documentos_passageiros',
        'declaracao_mercadorias_perigosas',
        
        // Status e controle
        'status',
        'decidido_por',
        
        // Relacionamentos
        'user_id'
    ];

    protected $casts = [
        'data_chegada' => 'date',
        'hora_chegada' => 'datetime:H:i',
        'data_partida' => 'date',
        'hora_partida' => 'datetime:H:i',
        'numero_tripulantes' => 'integer',
        'numero_passageiros' => 'integer',
        'certificados_navio' => 'array',
        'declaracoes_cargas' => 'array',
        'declaracao_provisoes_bordo' => 'array',
        'declaracao_pertences_tripulacao' => 'array',
        'documentos_tripulantes' => 'array',
        'documentos_passageiros' => 'array',
        'declaracao_mercadorias_perigosas' => 'array',
    ];

    protected $attributes = [
        'status' => 'pendente',
        'numero_tripulantes' => 0,
        'numero_passageiros' => 0,
    ];

    // Relacionamentos
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function decisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decidido_por');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(PedidoDocumento::class, 'pedido_id');
    }

    // Scopes
    public function scopePendente($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeEmAnalise($query)
    {
        return $query->where('status', 'em_analise');
    }

    public function scopeAprovado($query)
    {
        return $query->where('status', 'aprovado');
    }

    public function scopeRejeitado($query)
    {
        return $query->where('status', 'rejeitado');
    }

    public function scopeCancelado($query)
    {
        return $query->where('status', 'cancelado');
    }

    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorNavio($query, $nome)
    {
        return $query->where('nome_navio', 'like', "%{$nome}%");
    }

    public function scopePorIMO($query, $imo)
    {
        return $query->where('numero_imo', 'like', "%{$imo}%");
    }

    public function scopePorDataChegada($query, $data)
    {
        return $query->whereDate('data_chegada', $data);
    }

    // Métodos auxiliares
    public function podeSerEditado(): bool
    {
        return $this->status === 'pendente';
    }

    public function podeSerAprovado(): bool
    {
        return in_array($this->status, ['pendente', 'em_analise']);
    }

    public function podeSerRejeitado(): bool
    {
        return in_array($this->status, ['pendente', 'em_analise']);
    }

    public function getStatusFormatado(): string
    {
        return match($this->status) {
            'pendente' => 'Pendente',
            'em_analise' => 'Em Análise',
            'aprovado' => 'Aprovado',
            'rejeitado' => 'Rejeitado',
            'cancelado' => 'Cancelado',
            default => 'Desconhecido'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->getStatusFormatado();
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pendente' => 'bg-yellow-100 text-yellow-800',
            'em_analise' => 'bg-blue-100 text-blue-800',
            'aprovado' => 'bg-green-100 text-green-800',
            'rejeitado' => 'bg-red-100 text-red-800',
            'cancelado' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getDataChegadaFormatadaAttribute(): string
    {
        return $this->data_chegada ? $this->data_chegada->format('d/m/Y') : '';
    }

    public function getDataPartidaFormatadaAttribute(): string
    {
        return $this->data_partida ? $this->data_partida->format('d/m/Y') : '';
    }

    public function getHoraChegadaFormatadaAttribute(): string
    {
        return $this->hora_chegada ? Carbon::parse($this->hora_chegada)->format('H:i') : '';
    }

    public function getHoraPartidaFormatadaAttribute(): string
    {
        return $this->hora_partida ? Carbon::parse($this->hora_partida)->format('H:i') : '';
    }

    // Métodos para trabalhar com documentos
    public function adicionarCertificadoNavio(string $path, string $nome): void
    {
        $certificados = $this->certificados_navio ?? [];
        $certificados[] = ['nome' => $nome, 'path' => $path];
        $this->update(['certificados_navio' => $certificados]);
    }

    public function adicionarDeclaracaoCarga(string $path, string $nome): void
    {
        $declaracoes = $this->declaracoes_cargas ?? [];
        $declaracoes[] = ['nome' => $nome, 'path' => $path];
        $this->update(['declaracoes_cargas' => $declaracoes]);
    }

    public function adicionarDocumentoTripulante(string $nome, string $path): void
    {
        $documentos = $this->documentos_tripulantes ?? [];
        $documentos[] = ['nome' => $nome, 'documento_path' => $path];
        $this->update(['documentos_tripulantes' => $documentos]);
    }

    public function adicionarDocumentoPassageiro(string $nome, string $path): void
    {
        $documentos = $this->documentos_passageiros ?? [];
        $documentos[] = ['nome' => $nome, 'documento_path' => $path];
        $this->update(['documentos_passageiros' => $documentos]);
    }

    // Métodos para mudança de status
    public function aprovar(User $usuario = null): bool
    {
        if (!$this->podeSerAprovado()) {
            return false;
        }

        return $this->update([
            'status' => 'aprovado',
            'decidido_por' => $usuario ? $usuario->id : auth()->id()
        ]);
    }

    public function rejeitar(User $usuario = null): bool
    {
        if (!$this->podeSerRejeitado()) {
            return false;
        }

        return $this->update([
            'status' => 'rejeitado',
            'decidido_por' => $usuario ? $usuario->id : auth()->id()
        ]);
    }

    public function colocarEmAnalise(): bool
    {
        if ($this->status !== 'pendente') {
            return false;
        }

        return $this->update(['status' => 'em_analise']);
    }

    public function cancelar(): bool
    {
        if (!in_array($this->status, ['pendente', 'em_analise'])) {
            return false;
        }

        return $this->update(['status' => 'cancelado']);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pedido) {
            if (empty($pedido->user_id)) {
                $pedido->user_id = auth()->id();
            }
        });
    }
}