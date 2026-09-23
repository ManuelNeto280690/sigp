<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Services\BillingService;

class EntradaSaidaEmbarcacao extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'entrada_saida_embarcacoes';

    protected $fillable = [
        'embarcacao_id',
        'terminal_id',
        'user_id',
        'numero_movimento',
        'tipo_movimento',
        'data_programada',
        'data_efetiva',
        'agente_maritimo',
        'capitania_origem',
        'capitania_destino',
        'porto_origem',
        'porto_destino',
        'eta',
        'etd',
        'ata',
        'atd',
        'motivo',
        'berco',
        'guindaste_id',
        'estado_embarcacao',
        'documentos_apresentados',
        'status',
        'observacoes',
        'autorizado_por',
        'autorizado_em',
        'is_active',
        'motivo_revogacao',
        'revogado_por',
        'revogado_em',
        'eta_temperatura_c',
        'eta_vento_max_kph',
        'eta_rajada_kph',
        'eta_chuva_chance_pct',
        'eta_swell_altura_m',
        'eta_swell_periodo_s',
        'eta_swell_direcao',
        'eta_onda_altura_m',
        'eta_onda_direcao',
        'previsao_tempo_eta'
    ];

    protected $casts = [
        'data_programada' => 'datetime',
        'data_efetiva' => 'datetime',
        'autorizado_em' => 'datetime',
        'eta' => 'datetime',
        'etd' => 'datetime',
        'ata' => 'datetime',
        'atd' => 'datetime',
        'documentos_apresentados' => 'array',
        'previsao_tempo_eta' => 'array',
        'eta_temperatura_c' => 'float',
        'eta_vento_max_kph' => 'integer',
        'eta_rajada_kph' => 'integer',
        'eta_chuva_chance_pct' => 'integer',
        'eta_swell_altura_m' => 'float',
        'eta_swell_periodo_s' => 'float',
        'eta_onda_altura_m' => 'float',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'programado',
        'is_active' => true
    ];

    // Relacionamentos
    public function embarcacao(): BelongsTo
    {
        return $this->belongsTo(Embarcacao::class);
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    public function guindaste(): BelongsTo
    {
        return $this->belongsTo(Guindaste::class, 'guindaste_id');
    }

    public function facturas()
    {
        return $this->hasMany(\App\Models\Factura::class, 'entrada_saida_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function autorizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autorizado_por');
    }

    // Scopes
    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeEntradas(Builder $query): Builder
    {
        return $query->where('tipo_movimento', 'entrada');
    }

    public function scopeSaidas(Builder $query): Builder
    {
        return $query->where('tipo_movimento', 'saida');
    }

    public function scopePorStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeProgramados(Builder $query): Builder
    {
        return $query->where('status', 'programado');
    }

    public function scopeAutorizados(Builder $query): Builder
    {
        return $query->where('status', 'autorizado');
    }

    public function scopeEmAndamento(Builder $query): Builder
    {
        return $query->where('status', 'em_andamento');
    }

    public function scopeConcluidos(Builder $query): Builder
    {
        return $query->where('status', 'concluido');
    }

    public function scopeCancelados(Builder $query): Builder
    {
        return $query->where('status', 'cancelado');
    }

    public function scopePorPeriodo(Builder $query, Carbon $inicio, Carbon $fim): Builder
    {
        return $query->whereBetween('data_programada', [$inicio, $fim]);
    }

    public function scopeHoje(Builder $query): Builder
    {
        return $query->whereDate('data_programada', Carbon::today());
    }

    public function scopeProximosDias(Builder $query, int $dias = 7): Builder
    {
        return $query->whereBetween('data_programada', [
            Carbon::today(),
            Carbon::today()->addDays($dias)
        ]);
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

    public function isAtracado(): bool
    {
        return $this->status === 'atracado';
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
            'atracado' => 'bg-teal-100 text-teal-800',
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
            'atracado' => 'Atracado',
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

    public function isAtrasado(): bool
    {
        if ($this->isConcluido() || $this->isCancelado()) {
            return false;
        }
        
        return $this->data_programada->isPast();
    }

    // Métodos de ação
    public function autorizar(User $usuario, string $observacoes = null): bool
    {
        if (!$this->isProgramado()) {
            return false;
        }
        
        $this->update([
            'status' => 'autorizado',
            'autorizado_por' => $usuario->id,
            'autorizado_em' => now(),
            'observacoes' => $observacoes ?? $this->observacoes
        ]);
        
        $this->loadMissing('terminal','embarcacao');
        
        // Billing: Gera taxa de Entrada ao autorizar (conforme solicitado)
        if ($this->isEntrada()) {
            BillingService::generateForEntradaSaida($this, 'Entrada');
        }
        
        return true;
    }

    public function iniciar(): bool
    {
        if (!$this->isAutorizado()) {
            return false;
        }
        
        $this->update([
            'status' => 'em_andamento',
            'data_efetiva' => now()
        ]);
        
        // Billing de Entrada movido para autorizar()
        
        return true;
    }

    protected function shouldBillEstadiaOnStart(): bool
    {
        $estado = strtolower($this->estado_embarcacao ?? '');
        if (empty($this->berco)) return true;
        return in_array($estado, ['fundeadouro','ancorado','esperado']);
    }

    public function concluir(): bool
    {
        if (!$this->isEmAndamento()) {
            return false;
        }
        
        $this->update([
            'status' => 'concluido'
        ]);
        
        $this->loadMissing('terminal','embarcacao');
        if ($this->isEntrada()) {
            BillingService::generateForEntradaSaida($this, 'estadia');
            BillingService::generateForEntradaSaida($this, 'entrada');
        }
        if ($this->isSaida()) {
            BillingService::generateForEntradaSaida($this, 'estadia');
            BillingService::generateForEntradaSaida($this, 'entrada');
            BillingService::generateForEntradaSaida($this, 'saida');
        }
        
        return true;
    }

    public function cancelar(string $motivo): bool
    {
        if ($this->isConcluido()) {
            return false;
        }
        
        $this->update([
            'status' => 'cancelado',
            'observacoes' => $motivo
        ]);
        
        return true;
    }

    // Validações
    public static function rules($id = null): array
    {
        return [
            'embarcacao_id' => 'required|uuid|exists:embarcacoes,id',
            'terminal_id' => 'nullable|uuid|exists:terminais,id',
            'numero_movimento' => [
                'required',
                'string',
                'max:50',
                Rule::unique('entrada_saida_embarcacoes')->ignore($id)
            ],
            'tipo_movimento' => 'required|in:entrada,saida',
            'data_programada' => 'required|date|after_or_equal:today',
            'data_efetiva' => 'nullable|date',
            'berco' => 'nullable|string|max:50',
            'agente_maritimo' => 'nullable|string|max:255',
            'capitania_origem' => 'nullable|string|max:255',
            'capitania_destino' => 'nullable|string|max:255',
            'motivo' => 'nullable|string|max:1000',
            'estado_embarcacao' => 'required|string',
            'documentos_apresentados' => 'nullable|array',
            'status' => 'required|in:programado,autorizado,em_andamento,concluido,cancelado',
            'observacoes' => 'nullable|string|max:1000'
        ];
    }

    public static function messages(): array
    {
        return [
            'embarcacao_id.required' => 'A embarcação é obrigatória.',
            'embarcacao_id.exists' => 'A embarcação selecionada não existe.',
            'numero_movimento.required' => 'O número do movimento é obrigatório.',
            'numero_movimento.unique' => 'Este número de movimento já existe.',
            'tipo_movimento.required' => 'O tipo de movimento é obrigatório.',
            'tipo_movimento.in' => 'O tipo de movimento deve ser entrada ou saída.',
            'data_programada.required' => 'A data programada é obrigatória.',
            'data_programada.after_or_equal' => 'A data programada deve ser hoje ou futura.',
            'status.in' => 'Status inválido.'
        ];
    }

    // Métodos estáticos
    public static function proximosMovimentos(int $limite = 10)
    {
        return static::with(['embarcacao', 'terminal'])
            ->where('data_programada', '>=', now())
            ->orderBy('data_programada')
            ->limit($limite)
            ->get();
    }

    public static function movimentosHoje()
    {
        return static::with(['embarcacao', 'terminal'])
            ->whereDate('data_programada', today())
            ->orderBy('data_programada')
            ->get();
    }

    public static function estatisticas(Carbon $inicio = null, Carbon $fim = null)
    {
        $query = static::query();
        
        if ($inicio && $fim) {
            $query->whereBetween('data_programada', [$inicio, $fim]);
        }
        
        return [
            'total' => $query->count(),
            'entradas' => $query->clone()->where('tipo_movimento', 'entrada')->count(),
            'saidas' => $query->clone()->where('tipo_movimento', 'saida')->count(),
            'programados' => $query->clone()->where('status', 'programado')->count(),
            'autorizados' => $query->clone()->where('status', 'autorizado')->count(),
            'em_andamento' => $query->clone()->where('status', 'em_andamento')->count(),
            'concluidos' => $query->clone()->where('status', 'concluido')->count(),
            'cancelados' => $query->clone()->where('status', 'cancelado')->count()
        ];
    }

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

        // Métodos de verificação de permissão
    public function podeEditar(): bool
    {
        // Não pode editar se estiver concluído ou cancelado
        if ($this->isConcluido() || $this->isCancelado()) {
            return false;
        }
        
        // Pode editar se estiver programado, autorizado ou em andamento
        return in_array($this->status, ['programado', 'autorizado', 'em_andamento']);
    }

    public function podeAutorizar(): bool
    {
        return $this->isProgramado();
    }

    public function podeIniciar(): bool
    {
        return $this->isAutorizado();
    }

    public function podeAtracar(): bool
    {
        // Pode atracar se estiver autorizado ou em andamento (mas ainda não atracado)
        return ($this->isAutorizado() || $this->isEmAndamento()) && $this->estado_embarcacao !== 'atracado';
    }

    public function podeDesatracar(): bool
    {
        // Pode desatracar se estiver autorizado ou em andamento e o estado for atracado
        return ($this->isAutorizado() || $this->isEmAndamento()) && $this->estado_embarcacao === 'atracado';
    }

    public function podeConcluir(): bool
    {
        return $this->isEmAndamento();
    }

    public function podeCancelar(): bool
    {
        return !$this->isConcluido();
    }

    public function podeExcluir(): bool
    {
        // Não pode excluir se estiver concluído
        if ($this->isConcluido()) {
            return false;
        }
        
        // Pode excluir se estiver programado, autorizado, em andamento ou cancelado
        return in_array($this->status, ['programado', 'autorizado', 'em_andamento', 'cancelado', 'pendente']);
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