<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;



class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
    }

    /**
     * Display a listing of the resource.
     */
        public function index()
    {
        try {
            $notifications = \App\Models\Notification::where('notifiable_type', 'App\\Models\\User')
                                                   ->where('notifiable_id', Auth::id())
                                                   ->orderBy('created_at', 'desc')
                                                   ->limit(10)
                                                   ->get()
                                                   ->map(function($notification) {
                                                       $data = json_decode($notification->data, true);
                                                       return [
                                                           'id' => $notification->id,
                                                           'title' => $data['title'] ?? 'Notificação',
                                                           'message' => Str::limit($data['message'] ?? '', 100),
                                                           'priority' => $data['priority'] ?? 'normal',
                                                           'category' => $data['category'] ?? 'general',
                                                           'read_at' => $notification->read_at,
                                                           'created_at_human' => $notification->created_at->diffForHumans(),
                                                           'action_url' => $data['action_url'] ?? null
                                                       ];
                                                   });
            
            return response()->json(['notifications' => $notifications]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar notificações: ' . $e->getMessage());
            return response()->json(['notifications' => []]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification)
    {
        // Verificar se a notificação pertence ao usuário
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Acesso negado.');
        }

        try {
            // Marcar como lida se ainda não foi
            if (!$notification->lida_em) {
                $notification->update([
                    'lida_em' => now()
                ]);

                // Log da ação
                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'read',
                    'model' => 'Notification',
                    'model_id' => $notification->id,
                    'changes' => ['lida_em' => now()],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            $notification->load(['relatedModel']);

            return view('notifications.show', compact('notification'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao visualizar notificação: ' . $e->getMessage());
        }
    }

    /**
     * Marcar notificação como lida
     */
    public function markAsRead(Notification $notification)
    {
        try {
            if ($notification->user_id !== Auth::id()) {
                return response()->json(['error' => 'Não autorizado'], 403);
            }
            
            $notification->update(['read_at' => now()]);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Erro ao marcar notificação como lida: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }

    /**
     * Marcar notificação como não lida
     */
    public function markAsUnread(Notification $notification)
    {
        // Verificar se a notificação pertence ao usuário
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Acesso negado'], 403);
        }

        try {
            $notification->update([
                'lida_em' => null
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'mark_unread',
                'model' => 'Notification',
                'model_id' => $notification->id,
                'changes' => ['lida_em' => null],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notificação marcada como não lida'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar notificação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar todas as notificações como lidas
     */
   /**
     * Marcar todas as notificações como lidas
     */
    public function markAllAsRead()
    {
        try {
            Notification::where('user_id', Auth::id())
                       ->whereNull('read_at')
                       ->update(['read_at' => now()]);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Erro ao marcar todas as notificações como lidas: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        // Verificar se a notificação pertence ao usuário
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Acesso negado'], 403);
        }

        try {
            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'delete',
                'model' => 'Notification',
                'model_id' => $notification->id,
                'changes' => $notification->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notificação excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir notificação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir notificações selecionadas
     */
    public function destroySelected(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:notifications,id'
        ]);

        try {
            $deleted = Notification::where('user_id', Auth::id())
                                 ->whereIn('id', $request->ids)
                                 ->delete();

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'delete_selected',
                'model' => 'Notification',
                'model_id' => null,
                'changes' => ['deleted_count' => $deleted, 'ids' => $request->ids],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$deleted} notificações excluídas com sucesso"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir notificações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpar notificações antigas
     */
    public function clearOld(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365'
        ]);

        try {
            $cutoffDate = now()->subDays($request->days);
            
            $deleted = Notification::where('user_id', Auth::id())
                                 ->where('created_at', '<', $cutoffDate)
                                 ->delete();

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'clear_old',
                'model' => 'Notification',
                'model_id' => null,
                'changes' => ['deleted_count' => $deleted, 'days' => $request->days],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$deleted} notificações antigas excluídas"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar notificações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Configurações de notificação do usuário
     */
    public function settings()
    {
        $user = Auth::user();
        $settings = $user->notification_settings ?? [];

        return view('notifications.settings', compact('settings'));
    }

    /**
     * Atualizar configurações de notificação
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'notification_types' => 'array',
            'notification_types.*' => 'in:pedido,movimento,alerta,incidente,inspecao,infracao',
            'frequency' => 'in:immediate,hourly,daily,weekly',
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i',
        ]);

        try {
            $user = Auth::user();
            $oldSettings = $user->notification_settings;

            $settings = [
                'email_notifications' => $request->boolean('email_notifications'),
                'push_notifications' => $request->boolean('push_notifications'),
                'sms_notifications' => $request->boolean('sms_notifications'),
                'notification_types' => $request->notification_types ?? [],
                'frequency' => $request->frequency ?? 'immediate',
                'quiet_hours_start' => $request->quiet_hours_start,
                'quiet_hours_end' => $request->quiet_hours_end,
            ];

            $user->update([
                'notification_settings' => $settings
            ]);

            // Log da ação
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'update_settings',
                'model' => 'Notification',
                'model_id' => null,
                'changes' => [
                    'old' => $oldSettings,
                    'new' => $settings
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Configurações de notificação atualizadas com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao atualizar configurações: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint para notificações não lidas
     */
    /**
     * Contar notificações não lidas
     */
    public function getUnreadCount()
    {
        try {
            $count = \App\Models\Notification::where('notifiable_type', 'App\\Models\\User')
                                           ->where('notifiable_id', Auth::id())
                                           ->whereNull('read_at')
                                           ->count();
            
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            Log::error('Erro ao contar notificações: ' . $e->getMessage());
            return response()->json(['count' => 0]);
        }
    }


    /**
     * API endpoint para notificações recentes
     */
    public function recent(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            
            $notifications = Notification::where('user_id', Auth::id())
                                       ->orderBy('created_at', 'desc')
                                       ->limit($limit)
                                       ->get([
                                           'id', 'titulo', 'mensagem', 'tipo', 
                                           'prioridade', 'lida_em', 'created_at'
                                       ]);

            return response()->json([
                'success' => true,
                'data' => $notifications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar notificações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar notificação (método estático para uso interno)
     */
    public static function createNotification($userId, $data)
    {
        try {
            $notification = \App\Models\Notification::create([
                'type' => $data['type'] ?? 'App\\Notifications\\GeneralNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $userId,
                'data' => json_encode([
                    'title' => $data['titulo'] ?? $data['title'] ?? 'Notificação',
                    'message' => $data['mensagem'] ?? $data['message'] ?? '',
                    'priority' => $data['prioridade'] ?? $data['priority'] ?? 'normal',
                    'category' => $data['category'] ?? 'general',
                    'related_model' => $data['related_type'] ?? null,
                    'related_id' => $data['related_id'] ?? null,
                    'action_url' => $data['action_url'] ?? null,
                    'action_text' => $data['action_text'] ?? null,
                    'metadata' => $data['metadata'] ?? null,
                ])
            ]);

            // Enviar por email se configurado
            $user = \App\Models\User::find($userId);
            if ($user && ($user->notification_settings['email_notifications'] ?? false)) {
                static::sendEmailNotification($user, $notification);
            }

            // Limpar cache de contagem
            Cache::forget("user_{$userId}_unread_notifications");

            return $notification;
        } catch (\Exception $e) {
            \Log::error('Erro ao criar notificação: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Enviar notificação por email
     */
    private static function sendEmailNotification($user, $notification)
    {
        try {
            // Verificar horário silencioso
            if (static::isQuietHours($user)) {
                return;
            }

            // Implementar envio de email
            // Mail::to($user->email)->send(new NotificationMail($notification));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de notificação: ' . $e->getMessage());
        }
    }

    /**
     * Verificar se está em horário silencioso
     */
    private static function isQuietHours($user)
    {
        $settings = $user->notification_settings ?? [];
        
        if (!isset($settings['quiet_hours_start']) || !isset($settings['quiet_hours_end'])) {
            return false;
        }

        $now = now()->format('H:i');
        $start = $settings['quiet_hours_start'];
        $end = $settings['quiet_hours_end'];

        if ($start <= $end) {
            return $now >= $start && $now <= $end;
        } else {
            return $now >= $start || $now <= $end;
        }
    }

    /**
     * Notificar múltiplos usuários
     */
    public static function notifyUsers($userIds, $data)
    {
        try {
            $notifications = [];
            
            foreach ($userIds as $userId) {
                $notification = static::createNotification($userId, $data);
                if ($notification) {
                    $notifications[] = $notification;
                }
            }

            return $notifications;
        } catch (\Exception $e) {
            Log::error('Erro ao notificar usuários: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Notificar por role
     */
    public static function notifyByRole($role, $data)
    {
        try {
            $users = \App\Models\User::role($role)->pluck('id');
            return static::notifyUsers($users, $data);
        } catch (\Exception $e) {
            Log::error('Erro ao notificar por role: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Limpeza automática de notificações antigas
     */
    public static function cleanupOldNotifications($days = 90)
    {
        try {
            $cutoffDate = now()->subDays($days);
            
            $deleted = Notification::where('created_at', '<', $cutoffDate)
                                 ->where('lida_em', '<', $cutoffDate)
                                 ->delete();

            Log::info("Limpeza automática: {$deleted} notificações antigas removidas");
            
            return $deleted;
        } catch (\Exception $e) {
            Log::error('Erro na limpeza automática: ' . $e->getMessage());
            return 0;
        }
    }
}