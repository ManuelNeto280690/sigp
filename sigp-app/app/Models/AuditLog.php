<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'user_type',
        'event',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
        'tags',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'tags' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now();
            
            if (!$model->user_id && Auth::check()) {
                $model->user_id = Auth::id();
                $model->user_type = get_class(Auth::user());
            }
            
            if (!$model->ip_address) {
                $model->ip_address = Request::ip();
            }
            
            if (!$model->user_agent) {
                $model->user_agent = Request::userAgent();
            }
            
            if (!$model->url) {
                $model->url = Request::fullUrl();
            }
        });
    }

    // Relacionamentos
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    public function scopeByModel($query, $modelType)
    {
        return $query->where('auditable_type', $modelType);
    }

    public function scopeByModelId($query, $modelType, $modelId)
    {
        return $query->where('auditable_type', $modelType)
                    ->where('auditable_id', $modelId);
    }

    public function scopeByTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    public function scopeLoginEvents($query)
    {
        return $query->whereIn('event', ['login', 'logout', 'failed_login']);
    }

    public function scopeDataChanges($query)
    {
        return $query->whereIn('event', ['created', 'updated', 'deleted']);
    }

    public function scopeSecurityEvents($query)
    {
        return $query->whereIn('event', ['login', 'logout', 'failed_login', 'password_changed', 'account_locked']);
    }

    // Métodos auxiliares
    public function getEventBadgeClass(): string
    {
        return match($this->event) {
            'created' => 'bg-green-100 text-green-800',
            'updated' => 'bg-blue-100 text-blue-800',
            'deleted' => 'bg-red-100 text-red-800',
            'login' => 'bg-emerald-100 text-emerald-800',
            'logout' => 'bg-gray-100 text-gray-800',
            'failed_login' => 'bg-red-100 text-red-800',
            'password_changed' => 'bg-yellow-100 text-yellow-800',
            'account_locked' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getEventLabel(): string
    {
        return match($this->event) {
            'created' => 'Criado',
            'updated' => 'Atualizado',
            'deleted' => 'Excluído',
            'login' => 'Login',
            'logout' => 'Logout',
            'failed_login' => 'Falha no Login',
            'password_changed' => 'Senha Alterada',
            'account_locked' => 'Conta Bloqueada',
            default => ucfirst($this->event)
        };
    }

    public function getModelName(): string
    {
        if (!$this->auditable_type) {
            return 'N/A';
        }

        $modelName = class_basename($this->auditable_type);
        
        return match($modelName) {
            'User' => 'Usuário',
            'Embarcacao' => 'Embarcação',
            'Terminal' => 'Terminal',
            'Concessionaria' => 'Concessionária',
            'Declaracao' => 'Declaração',
            'Pedido' => 'Pedido',
            'Alerta' => 'Alerta',
            'Incidente' => 'Incidente',
            'InfracaoAmbiental' => 'Infração Ambiental',
            'Inspecao' => 'Inspeção',
            'Produto' => 'Produto',
            'MovimentoTerminal' => 'Movimento Terminal',
            'MovimentoProduto' => 'Movimento Produto',
            'Configuracao' => 'Configuração',
            default => $modelName
        };
    }

    public function hasAuditChanges(): bool
    {
        return !empty($this->old_values) || !empty($this->new_values);
    }

    public function getChangedFields(): array
    {
        if (!$this->hasAuditChanges()) {
            return [];
        }

        $oldValues = $this->old_values ?? [];
        $newValues = $this->new_values ?? [];
        
        return array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
    }

    public function getFieldChange($field): array
    {
        return [
            'old' => $this->old_values[$field] ?? null,
            'new' => $this->new_values[$field] ?? null,
        ];
    }

    public function addTag($tag): void
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
    }

    public function removeTag($tag): void
    {
        $tags = $this->tags ?? [];
        $tags = array_filter($tags, fn($t) => $t !== $tag);
        $this->update(['tags' => array_values($tags)]);
    }

    public function hasTag($tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    // Métodos estáticos para criação de logs
    public static function logEvent(string $event, $auditable = null, array $oldValues = [], array $newValues = [], array $tags = []): self
    {
        return self::create([
            'event' => $event,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'tags' => $tags,
        ]);
    }

    public static function logLogin($user): self
    {
        return self::logEvent('login', $user, [], [], ['authentication']);
    }

    public static function logLogout($user): self
    {
        return self::logEvent('logout', $user, [], [], ['authentication']);
    }

    public static function logFailedLogin($email, $ip = null): self
    {
        return self::create([
            'event' => 'failed_login',
            'old_values' => ['email' => $email],
            'ip_address' => $ip ?? Request::ip(),
            'tags' => ['authentication', 'security'],
        ]);
    }

    public static function logPasswordChange($user): self
    {
        return self::logEvent('password_changed', $user, [], [], ['security']);
    }

    public static function logAccountLocked($user): self
    {
        return self::logEvent('account_locked', $user, [], [], ['security']);
    }

    // Métodos estáticos para estatísticas
    public static function getTotalByEvent(string $event): int
    {
        return self::where('event', $event)->count();
    }

    public static function getTotalByUser($userId): int
    {
        return self::where('user_id', $userId)->count();
    }

    public static function getTotalByModel(string $modelType): int
    {
        return self::where('auditable_type', $modelType)->count();
    }

    public static function getEventsToday(): int
    {
        return self::today()->count();
    }

    public static function getEventsThisWeek(): int
    {
        return self::thisWeek()->count();
    }

    public static function getEventsThisMonth(): int
    {
        return self::thisMonth()->count();
    }

    public static function getLoginAttemptsToday(): int
    {
        return self::where('event', 'failed_login')
                  ->today()
                  ->count();
    }

    public static function getSecurityEventsToday(): int
    {
        return self::securityEvents()
                  ->today()
                  ->count();
    }

    public static function getMostActiveUsers(int $limit = 10): \Illuminate\Support\Collection
    {
        return self::selectRaw('user_id, COUNT(*) as total')
                  ->whereNotNull('user_id')
                  ->groupBy('user_id')
                  ->orderByDesc('total')
                  ->limit($limit)
                  ->with('user')
                  ->get();
    }

    public static function getMostAuditedModels(int $limit = 10): \Illuminate\Support\Collection
    {
        return self::selectRaw('auditable_type, COUNT(*) as total')
                  ->whereNotNull('auditable_type')
                  ->groupBy('auditable_type')
                  ->orderByDesc('total')
                  ->limit($limit)
                  ->get();
    }

    public static function getEventsByHour(Carbon $date = null): \Illuminate\Support\Collection
    {
        $date = $date ?? today();
        
        return self::selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
                  ->whereDate('created_at', $date)
                  ->groupBy('hour')
                  ->orderBy('hour')
                  ->get();
    }
}