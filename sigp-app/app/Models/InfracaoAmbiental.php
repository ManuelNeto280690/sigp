<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InfracaoAmbiental extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'infracoes_ambientais';

    protected $fillable = [
        'embarcacao_id',
        'terminal_id',
        'user_id',
        'numero_auto',
        'tipo_infracao',
        'descricao_infracao',
        'data_infracao',
        'local_infracao',
        'gravidade',
        'valor_multa',
        'medidas_corretivas',
        'status',
        'prazo_regularizacao',
        'observacoes',
        'evidencias',
        'aprovado_por',
        'aprovado_em',
        'is_active'
    ];

    protected $casts = [
        'data_infracao' => 'datetime',
        'prazo_regularizacao' => 'datetime',
        'aprovado_em' => 'datetime',
        'valor_multa' => 'decimal:2',
        'evidencias' => 'array',
        'is_active' => 'boolean'
    ];

    protected $attributes = [
        'gravidade' => 'media',
        'status' => 'registrada',
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

    public function inspetor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    // Scopes
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_infracao', $tipo);
    }

    public function scopePorGravidade($query, $gravidade)
    {
        return $query->where('gravidade', $gravidade);
    }

    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorEmbarcacao($query, $embarcacaoId)
    {
        return $query->where('embarcacao_id', $embarcacaoId);
    }

    public function scopePorTerminal($query, $terminalId)
    {
        return $query->where('terminal_id', $terminalId);
    }

    public function scopePorInspetor($query, $inspetorId)
    {
        return $query->where('user_id', $inspetorId);
    }

    public function scopeRegistradas($query)
    {
        return $query->where('status', 'registrada');
    }

    public function scopeNotificadas($query)
    {
        return $query->where('status', 'notificada');
    }

    public function scopeContestadas($query)
    {
        return $query->where('status', 'contestada');
    }

    public function scopePagas($query)
    {
        return $query->where('status', 'paga');
    }

    public function scopeCanceladas($query)
    {
        return $query->where('status', 'cancelada');
    }

    public function scopeLeves($query)
    {
        return $query->where('gravidade', 'leve');
    }

    public function scopeMedias($query)
    {
        return $query->where('gravidade', 'media');
    }

    public function scopeGraves($query)
    {
        return $query->where('gravidade', 'grave');
    }

    public function scopeGravissimas($query)
    {
        return $query->where('gravidade', 'gravissima');
    }

    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_infracao', [$dataInicio, $dataFim]);
    }

    public function scopeAprovadas($query)
    {
        return $query->whereNotNull('aprovado_por');
    }

    public function scopePendentesAprovacao($query)
    {
        return $query->whereNull('aprovado_por');
    }

    public function scopeVencendoEm($query, $dias)
    {
        return $query->where('prazo_regularizacao', '<=', now()->addDays($dias))
                    ->where('status', '!=', 'paga')
                    ->where('status', '!=', 'cancelada');
    }

    public function scopeComMulta($query)
    {
        return $query->whereNotNull('valor_multa');
    }

    public function scopeComEvidencias($query)
    {
        return $query->whereNotNull('evidencias');
    }

    public function scopeAtivas($query)
    {
        return $query->where('is_active', true);
    }

    // Métodos auxiliares
    public function isRegistrada(): bool
    {
        return $this->status === 'registrada';
    }

    public function isNotificada(): bool
    {
        return $this->status === 'notificada';
    }

    public function isContestada(): bool
    {
        return $this->status === 'contestada';
    }

    public function isPaga(): bool
    {
        return $this->status === 'paga';
    }

    public function isCancelada(): bool
    {
        return $this->status === 'cancelada';
    }

    public function isLeve(): bool
    {
        return $this->gravidade === 'leve';
    }

    public function isMedia(): bool
    {
        return $this->gravidade === 'media';
    }

    public function isGrave(): bool
    {
        return $this->gravidade === 'grave';
    }

    public function isGravissima(): bool
    {
        return $this->gravidade === 'gravissima';
    }

    public function isAprovada(): bool
    {
        return !is_null($this->aprovado_por);
    }

    public function isVencida(): bool
    {
        return $this->prazo_regularizacao && 
               $this->prazo_regularizacao->isPast() && 
               !$this->isPaga() && 
               !$this->isCancelada();
    }

    public function temMulta(): bool
    {
        return !is_null($this->valor_multa) && $this->valor_multa > 0;
    }

    public function temEvidencias(): bool
    {
        return !empty($this->evidencias);
    }

    public function diasParaVencimento(): ?int
    {
        if (!$this->prazo_regularizacao || $this->isPaga() || $this->isCancelada()) {
            return null;
        }
        
        return now()->diffInDays($this->prazo_regularizacao, false);
    }

    // Gestão de evidências
    public function adicionarEvidencia(array $evidencia): void
    {
        $evidencias = $this->evidencias ?? [];
        $evidencias[] = array_merge($evidencia, [
            'adicionada_em' => now()->toISOString(),
            'adicionada_por' => Auth::id()
        ]);
        $this->evidencias = $evidencias;
        $this->save();
    }

    public function removerEvidencia(int $indice): void
    {
        $evidencias = $this->evidencias ?? [];
        if (isset($evidencias[$indice])) {
            unset($evidencias[$indice]);
            $this->evidencias = array_values($evidencias);
            $this->save();
        }
    }

    public function getQuantidadeEvidencias(): int
    {
        return count($this->evidencias ?? []);
    }

    // Classes CSS para badges
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'registrada' => 'bg-yellow-100 text-yellow-800',
            'notificada' => 'bg-blue-100 text-blue-800',
            'contestada' => 'bg-orange-100 text-orange-800',
            'paga' => 'bg-green-100 text-green-800',
            'cancelada' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getGravidadeBadgeClass(): string
    {
        return match($this->gravidade) {
            'leve' => 'bg-green-100 text-green-800',
            'media' => 'bg-yellow-100 text-yellow-800',
            'grave' => 'bg-orange-100 text-orange-800',
            'gravissima' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getTipoInfracaoLabel(): string
    {
        return match($this->tipo_infracao) {
            'descarga_irregular' => 'Descarga Irregular',
            'poluicao_atmosferica' => 'Poluição Atmosférica',
            'residuos_solidos' => 'Resíduos Sólidos',
            'ruido_excessivo' => 'Ruído Excessivo',
            'outras' => 'Outras',
            default => 'Não Definido'
        };
    }

    // Métodos de ação
    public function notificar(): bool
    {
        if ($this->isRegistrada()) {
            $this->status = 'notificada';
            return $this->save();
        }
        return false;
    }

    public function contestar(): bool
    {
        if ($this->isNotificada()) {
            $this->status = 'contestada';
            return $this->save();
        }
        return false;
    }

    public function pagar(): bool
    {
        if (in_array($this->status, ['notificada', 'contestada'])) {
            $this->status = 'paga';
            return $this->save();
        }
        return false;
    }

    public function cancelar(): bool
    {
        if (!$this->isPaga()) {
            $this->status = 'cancelada';
            return $this->save();
        }
        return false;
    }

    public function aprovar(string $aprovadorId = null): bool
    {
        $this->aprovado_por = $aprovadorId ?? Auth::id();
        $this->aprovado_em = now();
        return $this->save();
    }

    // Métodos estáticos para contadores e estatísticas
    public static function totalPorStatus(): array
    {
        return self::selectRaw('status, COUNT(*) as total')
                  ->groupBy('status')
                  ->pluck('total', 'status')
                  ->toArray();
    }

    public static function totalPorGravidade(): array
    {
        return self::selectRaw('gravidade, COUNT(*) as total')
                  ->groupBy('gravidade')
                  ->pluck('total', 'gravidade')
                  ->toArray();
    }

    public static function totalPorTipo(): array
    {
        return self::selectRaw('tipo_infracao, COUNT(*) as total')
                  ->groupBy('tipo_infracao')
                  ->pluck('total', 'tipo_infracao')
                  ->toArray();
    }

    public static function valorTotalMultas(): float
    {
        return self::whereNotNull('valor_multa')->sum('valor_multa');
    }

    public static function valorMultasPagas(): float
    {
        return self::where('status', 'paga')
                  ->whereNotNull('valor_multa')
                  ->sum('valor_multa');
    }

    public static function valorMultasPendentes(): float
    {
        return self::whereIn('status', ['registrada', 'notificada', 'contestada'])
                  ->whereNotNull('valor_multa')
                  ->sum('valor_multa');
    }

    public static function infracoesHoje(): int
    {
        return self::whereDate('data_infracao', today())->count();
    }

    public static function infracoesMes(): int
    {
        return self::whereMonth('data_infracao', now()->month)
                  ->whereYear('data_infracao', now()->year)
                  ->count();
    }

    public static function infracoesVencendoEm($dias = 7): int
    {
        return self::vencendoEm($dias)->count();
    }

    // Geração automática de número do auto
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($infracao) {
            if (empty($infracao->numero_auto)) {
                $infracao->numero_auto = self::gerarNumeroAuto();
            }
            
            if (empty($infracao->user_id)) {
                $infracao->user_id = Auth::id();
            }
        });
    }

    private static function gerarNumeroAuto(): string
    {
        $ano = date('Y');
        $ultimoNumero = self::whereYear('created_at', $ano)
                           ->max('numero_auto');
        
        if ($ultimoNumero) {
            $numero = intval(substr($ultimoNumero, -6)) + 1;
        } else {
            $numero = 1;
        }
        
        return 'AI' . $ano . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    // Validações
    public static function validationRules($id = null): array
    {
        return [
            'embarcacao_id' => 'nullable|exists:embarcacoes,id',
            'terminal_id' => 'nullable|exists:terminais,id',
            'user_id' => 'required|exists:users,id',
            'numero_auto' => [
                'required',
                'string',
                'max:255',
                Rule::unique('infracoes_ambientais')->ignore($id)
            ],
            'tipo_infracao' => 'required|in:descarga_irregular,poluicao_atmosferica,residuos_solidos,ruido_excessivo,outras',
            'descricao_infracao' => 'required|string',
            'data_infracao' => 'required|date|before_or_equal:now',
            'local_infracao' => 'required|string|max:255',
            'gravidade' => 'required|in:leve,media,grave,gravissima',
            'valor_multa' => 'nullable|numeric|min:0|max:999999.99',
            'medidas_corretivas' => 'nullable|string',
            'status' => 'required|in:registrada,notificada,contestada,paga,cancelada',
            'prazo_regularizacao' => 'nullable|date|after:data_infracao',
            'observacoes' => 'nullable|string',
            'evidencias' => 'nullable|array',
            'aprovado_por' => 'nullable|exists:users,id',
            'is_active' => 'boolean'
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'embarcacao_id.exists' => 'A embarcação selecionada não existe.',
            'terminal_id.exists' => 'O terminal selecionado não existe.',
            'user_id.required' => 'O inspetor é obrigatório.',
            'user_id.exists' => 'O inspetor selecionado não existe.',
            'numero_auto.required' => 'O número do auto é obrigatório.',
            'numero_auto.unique' => 'Este número de auto já está em uso.',
            'tipo_infracao.required' => 'O tipo de infração é obrigatório.',
            'tipo_infracao.in' => 'Tipo de infração inválido.',
            'descricao_infracao.required' => 'A descrição da infração é obrigatória.',
            'data_infracao.required' => 'A data da infração é obrigatória.',
            'data_infracao.before_or_equal' => 'A data da infração não pode ser futura.',
            'local_infracao.required' => 'O local da infração é obrigatório.',
            'gravidade.required' => 'A gravidade é obrigatória.',
            'gravidade.in' => 'Gravidade inválida.',
            'valor_multa.numeric' => 'O valor da multa deve ser um número.',
            'valor_multa.min' => 'O valor da multa não pode ser negativo.',
            'valor_multa.max' => 'O valor da multa é muito alto.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'Status inválido.',
            'prazo_regularizacao.after' => 'O prazo de regularização deve ser posterior à data da infração.',
            'aprovado_por.exists' => 'O aprovador selecionado não existe.'
        ];
    }
}