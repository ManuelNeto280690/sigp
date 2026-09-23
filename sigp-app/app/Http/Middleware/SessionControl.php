<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSession;
use Symfony\Component\HttpFoundation\Response;

class SessionControl
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Verificar se o usuário ainda existe na base de dados
            if (!$user || !$user->exists) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
                return redirect()->route('login')->with('error', 'Usuário não encontrado.');
            }
            
            $currentSessionId = session()->getId();
            
            try {
                // Verificar se há uma sessão ativa diferente
                if ($user->current_session_id && $user->current_session_id !== $currentSessionId) {
                    // Terminar sessões anteriores do usuário
                    UserSession::where('user_id', $user->id)
                        ->where('session_id', '!=', $currentSessionId)
                        ->where('status', 'active')
                        ->update([
                            'status' => 'terminated',
                            'logout_at' => now(),
                            'logout_reason' => 'Nova sessão iniciada'
                        ]);
                }
                
                // Atualizar informações da sessão atual no usuário
                $user->update([
                    'current_session_id' => $currentSessionId,
                    'last_activity' => now(),
                    'last_login_ip' => $request->ip()
                ]);
                
                // Criar ou atualizar sessão usando o modelo UserSession
                $existingSession = UserSession::where('session_id', $currentSessionId)->first();
                
                if ($existingSession) {
                    // Atualizar sessão existente
                    $existingSession->update([
                        'last_activity' => now(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                } else {
                    // Criar nova sessão
                    UserSession::create([
                        'user_id' => $user->id,
                        'session_id' => $currentSessionId,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'login_at' => now(),
                        'last_activity' => now(),
                        'status' => 'active'
                    ]);
                }
                
            } catch (\Exception $e) {
                // Log do erro para debug
                \Log::error('Erro no SessionControl: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'session_id' => $currentSessionId,
                    'error' => $e->getMessage()
                ]);
                
                // Não interromper o fluxo, apenas registrar o erro
            }
            
            // Verificar timeout de inatividade (30 minutos)
            if ($user->last_activity && $user->last_activity->diffInMinutes(now()) > 30) {
                // Terminar sessão atual
                UserSession::where('session_id', $currentSessionId)
                    ->update([
                        'status' => 'expired',
                        'logout_at' => now(),
                        'logout_reason' => 'Sessão expirada por inatividade'
                    ]);
                
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('message', 'Sua sessão expirou devido à inatividade.');
            }
        }
        
        return $next($request);
    }
}