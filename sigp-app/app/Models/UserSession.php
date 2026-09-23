<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class UserSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'login_at',
        'last_activity',
        'logout_at',
        'status',
        'logout_reason',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'last_activity' => 'datetime',
        'logout_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->login_at) {
                $model->login_at = now();
            }
            
            if (!$model->last_activity) {
                $model->last_activity = now();
            }
            
            if (!$model->ip_address) {
                $model->ip_address = Request::ip();
            }
            
            if (!$model->user_agent) {
                $model->user_agent = Request::userAgent();
            }
            
            if (!$model->session_id) {
                $model->session_id = session()->getId();
            }
        });
    }

    // Relacionamentos
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeTerminated($query)
    {
        return $query->where('status', 'terminated');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    public function scopeBySessionId($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeLoggedInToday($query)
    {
        return $query->whereDate('login_at', today());
    }

    public function scopeLoggedInThisWeek($query)
    {
        return $query->whereBetween('login_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeLoggedInThisMonth($query)
    {
        return $query->whereMonth('login_at', now()->month)
                    ->whereYear('login_at', now()->year);
    }

    public function scopeInactiveSince($query, Carbon $since)
    {
        return $query->where('last_activity', '<', $since)
                    ->where('status', 'active');
    }

    public function scopeExpiredSessions($query, int $timeoutMinutes = 120)
    {
        return $query->where('last_activity', '<', now()->subMinutes($timeoutMinutes))
                    ->where('status', 'active');
    }

    public function scopeCurrentSession($query)
    {
        return $query->where('session_id', session()->getId())
                    ->where('status', 'active');
    }

    // Métodos auxiliares
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isTerminated(): bool
    {
        return $this->status === 'terminated';
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'expired' => 'bg-yellow-100 text-yellow-800',
            'terminated' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'active' => 'Ativa',
            'expired' => 'Expirada',
            'terminated' => 'Terminada',
            default => ucfirst($this->status)
        };
    }

    public function getDuration(): ?int
    {
        if (!$this->logout_at) {
            return null;
        }
        
        return $this->login_at->diffInMinutes($this->logout_at);
    }

    public function getDurationFormatted(): string
    {
        $duration = $this->getDuration();
        
        if (!$duration) {
            return 'Em andamento';
        }
        
        if ($duration < 60) {
            return $duration . ' minutos';
        }
        
        $hours = floor($duration / 60);
        $minutes = $duration % 60;
        
        return $hours . 'h ' . $minutes . 'm';
    }

    public function getInactiveTime(): int
    {
        if (!$this->last_activity) {
            return 0;
        }
        
        return $this->last_activity->diffInMinutes(now());
    }

    public function isInactive(int $timeoutMinutes = 120): bool
    {
        return $this->getInactiveTime() > $timeoutMinutes;
    }

    public function getBrowserName(): string
    {
        $userAgent = $this->user_agent;
        
        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            return 'Edge';
        } elseif (str_contains($userAgent, 'Opera')) {
            return 'Opera';
        }
        
        return 'Desconhecido';
    }

    public function getOperatingSystem(): string
    {
        $userAgent = $this->user_agent;
        
        if (str_contains($userAgent, 'Windows')) {
            return 'Windows';
        } elseif (str_contains($userAgent, 'Mac')) {
            return 'macOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            return 'Linux';
        } elseif (str_contains($userAgent, 'Android')) {
            return 'Android';
        } elseif (str_contains($userAgent, 'iOS')) {
            return 'iOS';
        }
        
        return 'Desconhecido';
    }

    public function isMobile(): bool
    {
        return str_contains($this->user_agent, 'Mobile') || 
               str_contains($this->user_agent, 'Android') || 
               str_contains($this->user_agent, 'iPhone');
    }

    public function updateActivity(): void
    {
        $this->update([
            'last_activity' => now()
        ]);
    }

    // Métodos de ação
    public function terminate(string $reason = null): bool
    {
        return $this->update([
            'status' => 'terminated',
            'logout_at' => now(),
            'logout_reason' => $reason ?? 'Sessão terminada pelo usuário'
        ]);
    }

    public function expire(string $reason = null): bool
    {
        return $this->update([
            'status' => 'expired',
            'logout_at' => now(),
            'logout_reason' => $reason ?? 'Sessão expirada por inatividade'
        ]);
    }

    public function logout(string $reason = null): bool
    {
        return $this->update([
            'status' => 'terminated',
            'logout_at' => now(),
            'logout_reason' => $reason ?? 'Logout normal'
        ]);
    }

    // Métodos estáticos
    public static function createSession($userId = null): self
    {
        return self::create([
            'user_id' => $userId ?? Auth::id(),
        ]);
    }

    public static function getCurrentSession(): ?self
    {
        return self::currentSession()->first();
    }

    public static function terminateAllUserSessions($userId, string $reason = null): int
    {
        return self::where('user_id', $userId)
                  ->where('status', 'active')
                  ->update([
                      'status' => 'terminated',
                      'logout_at' => now(),
                      'logout_reason' => $reason ?? 'Todas as sessões terminadas'
                  ]);
    }

    public static function expireInactiveSessions(int $timeoutMinutes = 120): int
    {
        return self::expiredSessions($timeoutMinutes)
                  ->update([
                      'status' => 'expired',
                      'logout_at' => now(),
                      'logout_reason' => 'Sessão expirada por inatividade (' . $timeoutMinutes . ' minutos)'
                  ]);
    }

    public static function cleanupOldSessions(int $daysOld = 30): int
    {
        return self::where('created_at', '<', now()->subDays($daysOld))
                  ->whereIn('status', ['expired', 'terminated'])
                  ->delete();
    }

    // Métodos estáticos para estatísticas
    public static function getTotalActiveSessions(): int
    {
        return self::active()->count();
    }

    public static function getTotalActiveSessionsByUser($userId): int
    {
        return self::active()->byUser($userId)->count();
    }

    public static function getSessionsToday(): int
    {
        return self::loggedInToday()->count();
    }

    public static function getSessionsThisWeek(): int
    {
        return self::loggedInThisWeek()->count();
    }

    public static function getSessionsThisMonth(): int
    {
        return self::loggedInThisMonth()->count();
    }

    public static function getAverageSessionDuration(): float
    {
        $sessions = self::whereNotNull('logout_at')
                       ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, login_at, logout_at)) as avg_duration')
                       ->first();
        
        return $sessions->avg_duration ?? 0;
    }

    public static function getMostActiveUsers(int $limit = 10): \Illuminate\Support\Collection
    {
        return self::selectRaw('user_id, COUNT(*) as session_count')
                  ->groupBy('user_id')
                  ->orderByDesc('session_count')
                  ->limit($limit)
                  ->with('user')
                  ->get();
    }

    public static function getSessionsByHour(Carbon $date = null): \Illuminate\Support\Collection
    {
        $date = $date ?? today();
        
        return self::selectRaw('HOUR(login_at) as hour, COUNT(*) as total')
                  ->whereDate('login_at', $date)
                  ->groupBy('hour')
                  ->orderBy('hour')
                  ->get();
    }

    public static function getUniqueIpsToday(): int
    {
        return self::loggedInToday()
                  ->distinct('ip_address')
                  ->count('ip_address');
    }

    public static function getBrowserStats(): \Illuminate\Support\Collection
    {
        return self::selectRaw('user_agent, COUNT(*) as total')
                  ->groupBy('user_agent')
                  ->orderByDesc('total')
                  ->get()
                  ->map(function ($session) {
                      $userSession = new self(['user_agent' => $session->user_agent]);
                      return [
                          'browser' => $userSession->getBrowserName(),
                          'os' => $userSession->getOperatingSystem(),
                          'total' => $session->total,
                          'is_mobile' => $userSession->isMobile()
                      ];
                  })
                  ->groupBy('browser')
                  ->map(function ($group) {
                      return $group->sum('total');
                  })
                  ->sortDesc();
    }
}