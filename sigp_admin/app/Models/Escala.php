<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Escala extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'escalas';

    protected $fillable = [
        'numero_escala',
        'embarcacao_id',
        'tipo_operacao',
        'berco',
        'eta_previsto',
        'etd_previsto',
        'ata_real',
        'atd_real',
        'status',
        'progresso_fals',
        'fals_obrigatorios_aprovados',
        'observacoes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'eta_previsto' => 'datetime',
        'etd_previsto' => 'datetime',
        'ata_real' => 'datetime',
        'atd_real' => 'datetime',
        'progresso_fals' => 'decimal:2',
        'fals_obrigatorios_aprovados' => 'boolean'
    ];

    protected $attributes = [
        'status' => 'planejada',
        'progresso_fals' => 0.00,
        'fals_obrigatorios_aprovados' => false
    ];

    // Relacionamentos
    public function embarcacao(): BelongsTo
    {
        return $this->belongsTo(Embarcacao::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    // Relacionamentos com FALs
    public function fal1(): HasOne
    {
        return $this->hasOne(Fal1DeclaracaoGeral::class, 'pedido_id');
    }

    public function fal2(): HasOne
    {
        return $this->hasOne(Fal2DeclaracaoCarga::class, 'pedido_id');
    }

    public function fal3(): HasOne
    {
        return $this->hasOne(Fal3ProvisoesBordo::class, 'pedido_id');
    }

    public function fal4(): HasOne
    {
        return $this->hasOne(Fal4PertencessTripulacao::class, 'pedido_id');
    }

    public function fal5(): HasOne
    {
        return $this->hasOne(Fal5ListaTripulantes::class, 'pedido_id');
    }

    public function fal6(): HasOne
    {
        return $this->hasOne(Fal6ListaPassageiros::class, 'pedido_id');
    }

    public function fal7(): HasOne
    {
        return $this->hasOne(Fal7MercadoriasPerigosas::class, 'pedido_id');
    }

    // Scopes
    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorTipoOperacao($query, $tipoOperacao)
    {
        return $query->where('tipo_operacao', $tipoOperacao);
    }

    public function scopePorTerminal($query, $terminalId)
    {
        return $query->where('terminal_id', $terminalId);
    }

    public function scopePorEmbarcacao($query, $embarcacaoId)
    {
        return $query->where('embarcacao_id', $embarcacaoId);
    }

    public function scopeAtivas($query)
    {
        return $query->whereIn('status', ['confirmada', 'atracada']);
    }

    public function scopePlanejadas($query)
    {
        return $query->where('status', 'planejada');
    }

    public function scopeConfirmadas($query)
    {
        return $query->where('status', 'confirmada');
    }

    public function scopeAtracadas($query)
    {
        return $query->where('status', 'atracada');
    }

    public function scopeDesatracadas($query)
    {
        return $query->where('status', 'desatracada');
    }

    public function scopeCanceladas($query)
    {
        return $query->where('status', 'cancelada');
    }

    // Métodos auxiliares
    public function isPlanejada(): bool
    {
        return $this->status === 'planejada';
    }

    public function isConfirmada(): bool
    {
        return $this->status === 'confirmada';
    }

    public function isAtracada(): bool
    {
        return $this->status === 'atracada';
    }

    public function isDesatracada(): bool
    {
        return $this->status === 'desatracada';
    }

    public function isCancelada(): bool
    {
        return $this->status === 'cancelada';
    }

    public function isAtiva(): bool
    {
        return in_array($this->status, ['confirmada', 'atracada']);
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'planejada' => 'bg-blue-100 text-blue-800',
            'confirmada' => 'bg-green-100 text-green-800',
            'atracada' => 'bg-purple-100 text-purple-800',
            'desatracada' => 'bg-gray-100 text-gray-800',
            'cancelada' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getProgressoFalsPercentual(): int
    {
        return (int) $this->progresso_fals;
    }

    public function getProgressoFalsClass(): string
    {
        $progresso = $this->getProgressoFalsPercentual();
        
        if ($progresso >= 100) {
            return 'bg-green-500';
        } elseif ($progresso >= 75) {
            return 'bg-blue-500';
        } elseif ($progresso >= 50) {
            return 'bg-yellow-500';
        } elseif ($progresso >= 25) {
            return 'bg-orange-500';
        } else {
            return 'bg-red-500';
        }
    }

    public function calcularProgressoFals(): float
    {
        $fals = [
            $this->fal1,
            $this->fal2,
            $this->fal3,
            $this->fal4,
            $this->fal5,
            $this->fal6,
            $this->fal7
        ];

        $totalFals = count($fals);
        $falsAprovados = 0;

        foreach ($fals as $fal) {
            if ($fal && $fal->status === 'aprovado') {
                $falsAprovados++;
            }
        }

        return $totalFals > 0 ? ($falsAprovados / $totalFals) * 100 : 0;
    }

    public function atualizarProgressoFals(): void
    {
        $progresso = $this->calcularProgressoFals();
        $this->update([
            'progresso_fals' => $progresso,
            'fals_obrigatorios_aprovados' => $progresso >= 100
        ]);
    }

    public function getDetalhesProgresso(): array
    {
        $fals = [
            'FAL1' => $this->fal1,
            'FAL2' => $this->fal2,
            'FAL3' => $this->fal3,
            'FAL4' => $this->fal4,
            'FAL5' => $this->fal5,
            'FAL6' => $this->fal6,
            'FAL7' => $this->fal7
        ];

        $detalhes = [];
        foreach ($fals as $nome => $fal) {
            $detalhes[$nome] = [
                'existe' => $fal !== null,
                'status' => $fal ? $fal->status_aprovacao : 'nao_iniciado',
                'aprovado' => $fal && $fal->status_aprovacao === 'aprovado'
            ];
        }

        return $detalhes;
    }

    // Geração automática de número da escala
    public static function gerarNumeroEscala(): string
    {
        $ano = date('Y');
        $ultimaEscala = static::whereYear('created_at', $ano)
            ->whereNotNull('numero_escala')
            ->orderBy('created_at', 'desc')
            ->first();

        $sequencial = 1;
        if ($ultimaEscala && $ultimaEscala->numero_escala) {
            $ultimoNumero = (int) substr($ultimaEscala->numero_escala, -6);
            $sequencial = $ultimoNumero + 1;
        }

        return 'ESC' . $ano . str_pad($sequencial, 6, '0', STR_PAD_LEFT);
    }

    // Boot method para gerar número automaticamente
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($escala) {
            if (empty($escala->numero_escala)) {
                $escala->numero_escala = static::gerarNumeroEscala();
            }
        });
    }
}