<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Log;


class TwoFactorController extends Controller
{
    /**
     * Show 2FA setup page
     */
    public function showSetup()
    {
        $user = Auth::user();
        
        // Se não tem segredo 2FA, gera um novo
        if (!$user->two_factor_secret) {
            $secret = $user->generateTwoFactorSecret();
            $user->update(['two_factor_secret' => $secret]);
            // Recarrega o usuário para garantir que o segredo foi salvo
            $user->refresh();
        }
        
        $qrCodeSvg = $user->getTwoFactorQrCodeSvg();
        
        return view('auth.2fa.setup', [
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $user->two_factor_secret
        ]);
    }
    
    /**
     * Enable 2FA
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);
        
        $user = Auth::user();
        
        if (!$user->verifyTwoFactor($request->code)) {
            throw ValidationException::withMessages([
                'code' => 'Código de verificação inválido.'
            ]);
        }
        
        // Se estava pendente, completar a configuração
        if ($user->two_factor_setup_pending) {
            $user->completeTwoFactorSetup();
        } else {
            $user->enableTwoFactor();
        }
        
        session(['2fa_verified' => true]);
        
        return redirect()->route('dashboard')
            ->with('success', '2FA configurado e habilitado com sucesso!');
    }
    
    /**
     * Show 2FA verification page
     */
    public function showVerify()
    {
        $user = Auth::user();
        
        Log::info('Página de verificação 2FA acessada', [
            'user_id' => $user->id, 
            'email' => $user->email,
            'method' => $user->two_factor_method
        ]);
        
        // Se o método for email, gerar e enviar código
        if ($user->two_factor_method === 'email') {
            Log::info('Gerando código 2FA por email', ['user_id' => $user->id]);
            
            try {
                $user->generateEmailTwoFactorCode();
                Log::info('Código 2FA por email gerado e enviado com sucesso', ['user_id' => $user->id]);
            } catch (\Exception $e) {
                Log::error('Erro ao gerar/enviar código 2FA por email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            return view('auth.2fa.verify', [
                'method' => 'email',
                'email' => $user->email
            ]);
        }
        
        Log::info('Exibindo verificação 2FA por authenticator', ['user_id' => $user->id]);
        return view('auth.2fa.verify', [
            'method' => 'authenticator'
        ]);
    }
    
    public function resendEmailCode()
    {
        $user = Auth::user();
        
        Log::info('Solicitação de reenvio de código 2FA por email', ['user_id' => $user->id]);
        
        if ($user->two_factor_method !== 'email') {
            Log::warning('Tentativa de reenvio com método inválido', [
                'user_id' => $user->id,
                'method' => $user->two_factor_method
            ]);
            return response()->json(['error' => 'Método inválido'], 400);
        }
        
        try {
            $user->generateEmailTwoFactorCode();
            Log::info('Código 2FA reenviado com sucesso', ['user_id' => $user->id]);
            return response()->json(['message' => 'Código reenviado com sucesso']);
        } catch (\Exception $e) {
            Log::error('Erro ao reenviar código 2FA por email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erro ao reenviar código'], 500);
        }
    }
    
    /**
     * Verify 2FA code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);
        
        $user = Auth::user();
        
        if (!$user->verifyTwoFactor($request->code)) {
            $user->incrementFailedAttempts();
            
            throw ValidationException::withMessages([
                'code' => 'Código de verificação inválido.'
            ]);
        }
        
        // Limpar código de email se foi usado
        if ($user->two_factor_method === 'email') {
            $user->clearEmailTwoFactorCode();
        }
        
        $user->resetFailedAttempts();
        $user->updateLastLogin($request->ip());
        
        session(['2fa_verified' => true]);
        
        return redirect()->intended('dashboard');
    }
    
    /**
     * Resend email code
     */
    public function resendEmailCode()
    {
        $user = Auth::user();
        
        if ($user->two_factor_method !== 'email') {
            return response()->json(['error' => 'Método inválido'], 400);
        }
        
        $user->generateEmailTwoFactorCode();
        
        return response()->json(['message' => 'Código reenviado com sucesso']);
    }
    
    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
            'code' => 'required|string|size:6'
        ]);
        
        $user = Auth::user();
        
        if (!$user->verifyTwoFactor($request->code)) {
            throw ValidationException::withMessages([
                'code' => 'Código de verificação inválido.'
            ]);
        }
        
        $user->disableTwoFactor();
        
        return redirect()->route('profile.edit')
            ->with('success', '2FA desabilitado com sucesso!');
    }
}