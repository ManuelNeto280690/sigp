<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class Notification extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('notifiable_type', 'App\\Models\\User')
                    ->where('notifiable_id', $userId);
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

    public function scopeByPriority($query, $priority)
    {
        return $query->whereJsonContains('data->priority', $priority);
    }

    public function scopeHighPriority($query)
    {
        return $query->byPriority('high');
    }

    public function scopeMediumPriority($query)
    {
        return $query->byPriority('medium');
    }

    public function scopeLowPriority($query)
    {
        return $query->byPriority('low');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->whereJsonContains('data->category', $category);
    }

    public function scopeSystemNotifications($query)
    {
        return $query->byCategory('system');
    }

    public function scopeSecurityNotifications($query)
    {
        return $query->byCategory('security');
    }

    public function scopeAlertNotifications($query)
    {
        return $query->byCategory('alert');
    }

    public function scopeIncidentNotifications($query)
    {
        return $query->byCategory('incident');
    }

    public function scopeInspectionNotifications($query)
    {
        return $query->byCategory('inspection');
    }

    // Métodos auxiliares
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    public function getTitle(): string
    {
        return $this->data['title'] ?? 'Notificação';
    }

    public function getMessage(): string
    {
        return $this->data['message'] ?? '';
    }

    public function getPriority(): string
    {
        return $this->data['priority'] ?? 'medium';
    }

    public function getCategory(): string
    {
        return $this->data['category'] ?? 'general';
    }

    public function getIcon(): string
    {
        return match($this->getCategory()) {
            'system' => 'cog',
            'security' => 'shield-exclamation',
            'alert' => 'exclamation-triangle',
            'incident' => 'fire',
            'inspection' => 'clipboard-check',
            'embarcacao' => 'ship',
            'terminal' => 'building-office',
            'user' => 'user',
            default => 'bell'
        };
    }

    public function getPriorityBadgeClass(): string
    {
        return match($this->getPriority()) {
            'high' => 'bg-red-100 text-red-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'low' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getCategoryBadgeClass(): string
    {
        return match($this->getCategory()) {
            'system' => 'bg-blue-100 text-blue-800',
            'security' => 'bg-red-100 text-red-800',
            'alert' => 'bg-orange-100 text-orange-800',
            'incident' => 'bg-red-100 text-red-800',
            'inspection' => 'bg-purple-100 text-purple-800',
            'embarcacao' => 'bg-cyan-100 text-cyan-800',
            'terminal' => 'bg-indigo-100 text-indigo-800',
            'user' => 'bg-emerald-100 text-emerald-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getPriorityLabel(): string
    {
        return match($this->getPriority()) {
            'high' => 'Alta',
            'medium' => 'Média',
            'low' => 'Baixa',
            default => 'Normal'
        };
    }

    public function getCategoryLabel(): string
    {
        return match($this->getCategory()) {
            'system' => 'Sistema',
            'security' => 'Segurança',
            'alert' => 'Alerta',
            'incident' => 'Incidente',
            'inspection' => 'Inspeção',
            'embarcacao' => 'Embarcação',
            'terminal' => 'Terminal',
            'user' => 'Usuário',
            default => 'Geral'
        };
    }

    public function getTypeLabel(): string
    {
        $type = class_basename($this->type);
        
        return match($type) {
            'WelcomeNotification' => 'Boas-vindas',
            'AlertCreatedNotification' => 'Alerta Criado',
            'IncidentReportedNotification' => 'Incidente Reportado',
            'InspectionScheduledNotification' => 'Inspeção Agendada',
            'InspectionCompletedNotification' => 'Inspeção Concluída',
            'EmbarcacaoArrivedNotification' => 'Embarcação Chegou',
            'EmbarcacaoDepartedNotification' => 'Embarcação Partiu',
            'SecurityAlertNotification' => 'Alerta de Segurança',
            'SystemMaintenanceNotification' => 'Manutenção do Sistema',
            'PasswordExpiringNotification' => 'Senha Expirando',
            'AccountLockedNotification' => 'Conta Bloqueada',
            default => $type
        };
    }

    public function getUrl(): ?string
    {
        return $this->data['url'] ?? null;
    }

    public function getActionText(): ?string
    {
        return $this->data['action_text'] ?? null;
    }

    public function getActionUrl(): ?string
    {
        return $this->data['action_url'] ?? null;
    }

    public function getRelatedModel(): ?string
    {
        return $this->data['related_model'] ?? null;
    }

    public function getRelatedId(): ?string
    {
        return $this->data['related_id'] ?? null;
    }

    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function isExpired(): bool
    {
        $expiresAt = $this->data['expires_at'] ?? null;
        
        if (!$expiresAt) {
            return false;
        }
        
        return Carbon::parse($expiresAt)->isPast();
    }

    // Métodos de ação
    public function markAsRead(): bool
    {
        if ($this->isRead()) {
            return true;
        }
        
        return $this->update(['read_at' => now()]);
    }

    public function markAsUnread(): bool
    {
        if ($this->isUnread()) {
            return true;
        }
        
        return $this->update(['read_at' => null]);
    }

    // Métodos estáticos
    public static function createNotification(
        $notifiable,
        string $type,
        array $data = [],
        string $priority = 'medium',
        string $category = 'general'
    ): self {
        return self::create([
            'type' => $type,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id,
            'data' => array_merge($data, [
                'priority' => $priority,
                'category' => $category,
                'created_by' => auth()->id(),
            ])
        ]);
    }

    public static function notifyUser(
        $user,
        string $title,
        string $message,
        string $type = 'general',
        string $priority = 'medium',
        string $category = 'general',
        array $additionalData = []
    ): self {
        return self::createNotification(
            $user,
            $type,
            array_merge([
                'title' => $title,
                'message' => $message,
            ], $additionalData),
            $priority,
            $category
        );
    }

    public static function notifyAllUsers(
        string $title,
        string $message,
        string $type = 'system',
        string $priority = 'medium',
        string $category = 'system'
    ): int {
        $users = \App\Models\User::all();
        $count = 0;
        
        foreach ($users as $user) {
            self::notifyUser($user, $title, $message, $type, $priority, $category);
            $count++;
        }
        
        return $count;
    }

    public static function notifyByRole(
        string $role,
        string $title,
        string $message,
        string $type = 'system',
        string $priority = 'medium',
        string $category = 'system'
    ): int {
        $users = \App\Models\User::role($role)->get();
        $count = 0;
        
        foreach ($users as $user) {
            self::notifyUser($user, $title, $message, $type, $priority, $category);
            $count++;
        }
        
        return $count;
    }

    public static function markAllAsReadForUser($userId): int
    {
        return self::forUser($userId)
                  ->unread()
                  ->update(['read_at' => now()]);
    }

    public static function deleteReadNotifications($userId, int $daysOld = 30): int
    {
        return self::forUser($userId)
                  ->read()
                  ->where('read_at', '<', now()->subDays($daysOld))
                  ->delete();
    }

    public static function deleteExpiredNotifications(): int
    {
        return self::whereNotNull('data->expires_at')
                  ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(data, "$.expires_at")) < ?', [now()])
                  ->delete();
    }

    // Métodos estáticos para estatísticas
    public static function getTotalUnreadForUser($userId): int
    {
        return self::forUser($userId)->unread()->count();
    }

    public static function getTotalByPriority(string $priority): int
    {
        return self::byPriority($priority)->count();
    }

    public static function getTotalByCategory(string $category): int
    {
        return self::byCategory($category)->count();
    }

    public static function getNotificationsToday(): int
    {
        return self::today()->count();
    }

    public static function getNotificationsThisWeek(): int
    {
        return self::thisWeek()->count();
    }

    public static function getNotificationsThisMonth(): int
    {
        return self::thisMonth()->count();
    }

    public static function getUnreadByPriority(): \Illuminate\Support\Collection
    {
        return self::unread()
                  ->selectRaw('JSON_UNQUOTE(JSON_EXTRACT(data, "$.priority")) as priority, COUNT(*) as total')
                  ->groupBy('priority')
                  ->orderByRaw('FIELD(priority, "high", "medium", "low")')
                  ->get();
    }

    public static function getNotificationsByCategory(): \Illuminate\Support\Collection
    {
        return self::selectRaw('JSON_UNQUOTE(JSON_EXTRACT(data, "$.category")) as category, COUNT(*) as total')
                  ->groupBy('category')
                  ->orderByDesc('total')
                  ->get();
    }

    public static function getMostActiveUsers(int $limit = 10): \Illuminate\Support\Collection
    {
        return self::selectRaw('notifiable_id, COUNT(*) as notification_count')
                  ->where('notifiable_type', 'App\\Models\\User')
                  ->groupBy('notifiable_id')
                  ->orderByDesc('notification_count')
                  ->limit($limit)
                  ->with('notifiable')
                  ->get();
    }

    public static function getNotificationsByHour(Carbon $date = null): \Illuminate\Support\Collection
    {
        $date = $date ?? today();
        
        return self::selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
                  ->whereDate('created_at', $date)
                  ->groupBy('hour')
                  ->orderBy('hour')
                  ->get();
    }

    public static function getReadRate(): float
    {
        $total = self::count();
        
        if ($total === 0) {
            return 0;
        }
        
        $read = self::read()->count();
        
        return round(($read / $total) * 100, 2);
    }

    public static function getAverageReadTime(): ?float
    {
        $notifications = self::read()
                            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, read_at)) as avg_read_time')
                            ->first();
        
        return $notifications->avg_read_time;
    }
}