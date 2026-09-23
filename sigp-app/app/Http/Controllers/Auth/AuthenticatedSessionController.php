<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Verificar o role do usuário e redirecionar adequadamente
        $user = Auth::user();
        
        // Carregar os roles do usuário
        $userRoles = $user->roles->pluck('name')->toArray();
        
        if (in_array('concessionaria', $userRoles)) {
            return redirect()->route('concessionarias.embarcacoes.index');
        } elseif (in_array('agente_navio', $userRoles)) {
            return redirect()->route('pedidos.index');
        }
        
        // Para qualquer outro role, redirecionar para o dashboard
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
