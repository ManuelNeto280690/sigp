<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Require2FA
{
    public function handle(Request $request, Closure $next)
    {
        // Verificar se o usuário está autenticado
        if (!Auth::check()) {
            Log::info('2FA Middleware: Usuário não autenticado, redirecionando para login');
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Verificar se o usuário tem 2FA habilitado
        if (!$user->two_factor_enabled) {
            Log::info('2FA Middleware: 2FA não habilitado para usuário ' . $user->id);
            return $next($request);
        }

        // Verificar se já passou pela verificação 2FA nesta sessão
        if (session('2fa_verified')) {
            Log::info('2FA Middleware: Sessão já verificada para usuário ' . $user->id);
            return $next($request);
        }

        // Se está tentando acessar rotas de 2FA, permitir
        if ($request->routeIs('2fa.*')) {
            Log::info('2FA Middleware: Acesso permitido para rota 2FA');
            return $next($request);
        }

        // Redirecionar para verificação 2FA
        Log::info('2FA Middleware: Redirecionando para verificação 2FA');
        return redirect()->route('2fa.verify');
    }
}