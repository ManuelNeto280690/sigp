<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Alerta extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'alertas';

    protected $fillable = [
        'user_id',
        'titulo',
        'descricao',
        'tipo',
        'nivel',
        'status',
        'data_inicio',
        'data_fim',
        'areas_afetadas',
        'acoes_tomadas',
        'resolvido_por',
        'resolvido_em',
        'notificar_usuarios',
        'is_active'
    ];

    protected $casts = [
        'areas_afetadas' => 'array',
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'resolvido_em' => 'datetime',
        'notificar_usuarios' => 'boolean',
        'is_active' => 'boolean'
    ];

    protected $attributes = [
        'nivel' => 'info',
        'status' => 'ativo',
        'notificar_usuarios' => true,
        'is_active' => true
    ];

    // Relacionamentos
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function resolvidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolvido_por');
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

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorNivel($query, $nivel)
    {
        return $query->where('nivel', $nivel);
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopeResolvidos($query)
    {
        return $query->where('status', 'resolvido');
    }

    public function scopeCancelados($query)
    {
        return $query->where('status', 'cancelado');
    }

    public function scopeCriticos($query)
    {
        return $query->whereIn('nivel', ['critico', 'emergencia']);
    }

    public function scopeEmergencia($query)
    {
        return $query->where('nivel', 'emergencia');
    }

    public function scopeSeguranca($query)
    {
        return $query->where('tipo', 'seguranca');
    }

    public function scopeAmbiental($query)
    {
        return $query->where('tipo', 'ambiental');
    }

    public function scopeOperacional($query)
    {
        return $query->where('tipo', 'operacional');
    }

    public function scopeVigentes($query)
    {
        return $query->where('status', 'ativo')
                    ->where('data_inicio', '<=', now())
                    ->where(function($q) {
                        $q->whereNull('data_fim')
                          ->orWhere('data_fim', '>=', now());
                    });
    }

    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_inicio', [$dataInicio, $dataFim]);
    }

    public function scopeComNotificacao($query)
    {
        return $query->where('notificar_usuarios', true);
    }

    // Métodos auxiliares
    public function isAtivo(): bool
    {
        return $this->status === 'ativo';
    }

    public function isResolvido(): bool
    {
        return $this->status === 'resolvido';
    }

    public function isCancelado(): bool
    {
        return $this->status === 'cancelado';
    }

    public function isCritico(): bool
    {
        return in_array($this->nivel, ['critico', 'emergencia']);
    }

    public function isEmergencia(): bool
    {
        return $this->nivel === 'emergencia';
    }

    public function isVigente(): bool
    {
        if (!$this->isAtivo()) {
            return false;
        }
        
        $agora = now();
        
        if ($this->data_inicio->isFuture()) {
            return false;
        }
        
        if ($this->data_fim && $this->data_fim->isPast()) {
            return false;
        }
        
        return true;
    }

    public function isVencido(): bool
    {
        return $this->data_fim && $this->data_fim->isPast() && $this->isAtivo();
    }

    public function tempoAtivo(): ?int
    {
        if (!$this->isAtivo()) {
            return null;
        }
        
        $inicio = $this->data_inicio;
        $fim = $this->data_fim ?? now();
        
        return $inicio->diffInMinutes($fim);
    }

    public function tempoResolucao(): ?int
    {
        if (!$this->resolvido_em) {
            return null;
        }
        
        return $this->data_inicio->diffInMinutes($this->resolvido_em);
    }

    public function diasRestantes(): ?int
    {
        if (!$this->data_fim || !$this->isAtivo()) {
            return null;
        }
        
        return now()->diffInDays($this->data_fim, false);
    }

    public function getNivelBadgeClass(): string
    {
        return match($this->nivel) {
            'baixa' => 'bg-blue-100 text-blue-800',
            'media' => 'bg-yellow-100 text-yellow-800',
            'critica' => 'bg-red-100 text-red-800',
            'alta' => 'bg-red-600 text-white',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'ativo' => 'bg-green-100 text-green-800',
            'resolvido' => 'bg-gray-100 text-gray-800',
            'cancelado' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getTipoBadgeClass(): string
    {
        return match($this->tipo) {
            'seguranca' => 'bg-red-100 text-red-800',
            'ambiental' => 'bg-green-100 text-green-800',
            'operacional' => 'bg-blue-100 text-blue-800',
            'manutencao' => 'bg-orange-100 text-orange-800',
            'emergencia' => 'bg-red-600 text-white',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    // Métodos de ação
    public function resolver($userId, $acoes = null): bool
    {
        if (!$this->isAtivo()) {
            return false;
        }

        $this->update([
            'status' => 'resolvido',
            'resolvido_por' => $userId,
            'resolvido_em' => now(),
            'acoes_tomadas' => $acoes ?? $this->acoes_tomadas
        ]);

        return true;
    }

    public function cancelar(): bool
    {
        if (!$this->isAtivo()) {
            return false;
        }

        $this->update(['status' => 'cancelado']);
        return true;
    }

    public function reativar(): bool
    {
        if ($this->isAtivo()) {
            return false;
        }

        $this->update([
            'status' => 'ativo',
            'resolvido_por' => null,
            'resolvido_em' => null
        ]);

        return true;
    }

    public function estenderPrazo($novaDataFim): bool
    {
        if (!$this->isAtivo()) {
            return false;
        }

        $this->update(['data_fim' => $novaDataFim]);
        return true;
    }

    // Métodos para áreas afetadas
    public function adicionarAreaAfetada($area): void
    {
        $areas = $this->areas_afetadas ?? [];
        if (!in_array($area, $areas)) {
            $areas[] = $area;
            $this->update(['areas_afetadas' => $areas]);
        }
    }

    public function removerAreaAfetada($area): void
    {
        $areas = $this->areas_afetadas ?? [];
        $areas = array_filter($areas, fn($a) => $a !== $area);
        $this->update(['areas_afetadas' => array_values($areas)]);
    }

    public function temAreaAfetada($area): bool
    {
        return in_array($area, $this->areas_afetadas ?? []);
    }

    public function getAreasAfetadasTexto(): string
    {
        if (empty($this->areas_afetadas)) {
            return 'Nenhuma área especificada';
        }
        
        return implode(', ', $this->areas_afetadas);
    }

    // Métodos estáticos
    public static function alertasAtivos(): int
    {
        return static::ativo()->count();
    }

    public static function alertasCriticos(): int
    {
        return static::criticos()->ativo()->count();
    }

    public static function alertasEmergencia(): int
    {
        return static::emergencia()->ativo()->count();
    }

    public static function alertasPorTipo(): array
    {
        return static::ativo()
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->toArray();
    }

    // Geração automática de dados
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($alerta) {
            if (empty($alerta->data_inicio)) {
                $alerta->data_inicio = now();
            }
        });
    }

    // Validações
    public static function validationRules($id = null): array
    {
        return [
            'user_id' => 'required|uuid|exists:users,id',
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string|max:2000',
            'tipo' => 'required|in:seguranca,ambiental,operacional,manutencao,outro',
            'nivel' => 'required|in:info,aviso,critico,emergencia',
            'status' => 'required|in:ativo,resolvido,cancelado',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after:data_inicio',
            'areas_afetadas' => 'nullable|array',
            'areas_afetadas.*' => 'string|max:255',
            'acoes_tomadas' => 'nullable|string|max:2000',
            'resolvido_por' => 'nullable|uuid|exists:users,id',
            'resolvido_em' => 'nullable|date',
            'notificar_usuarios' => 'boolean',
            'is_active' => 'boolean'
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'user_id.required' => 'O usuário criador é obrigatório.',
            'user_id.exists' => 'O usuário selecionado não existe.',
            'titulo.required' => 'O título é obrigatório.',
            'titulo.max' => 'O título não pode ter mais de 255 caracteres.',
            'descricao.required' => 'A descrição é obrigatória.',
            'descricao.max' => 'A descrição não pode ter mais de 2000 caracteres.',
            'tipo.required' => 'O tipo do alerta é obrigatório.',
            'tipo.in' => 'O tipo deve ser: segurança, ambiental, operacional, manutenção ou emergência.',
            'nivel.required' => 'O nível do alerta é obrigatório.',
            'nivel.in' => 'O nível deve ser: info, aviso, crítico ou emergência.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser: ativo, resolvido ou cancelado.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_inicio.date' => 'A data de início deve ser uma data válida.',
            'data_fim.date' => 'A data de fim deve ser uma data válida.',
            'data_fim.after' => 'A data de fim deve ser posterior à data de início.',
            'areas_afetadas.array' => 'As áreas afetadas devem ser uma lista.',
            'areas_afetadas.*.string' => 'Cada área afetada deve ser um texto.',
            'areas_afetadas.*.max' => 'Cada área afetada não pode ter mais de 255 caracteres.',
            'acoes_tomadas.max' => 'As ações tomadas não podem ter mais de 2000 caracteres.',
            'resolvido_por.exists' => 'O usuário que resolveu não existe.',
            'resolvido_em.date' => 'A data de resolução deve ser uma data válida.'
        ];
    }

    public function validate(array $data = null): \Illuminate\Validation\Validator
    {
        $data = $data ?? $this->toArray();
        return Validator::make($data, static::validationRules($this->id), static::validationMessages());
    }
}