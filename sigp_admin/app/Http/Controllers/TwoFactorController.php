<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class TwoFactorController extends Controller
{
    protected $google2fa;
    
    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }
    
    /**
     * Mostrar página de configuração do 2FA
     */
    public function showSetup()
    {
        $user = Auth::user();
        
        Log::info('2FA Setup iniciado', ['user_id' => $user->id, 'email' => $user->email]);
        
        if ($user->two_factor_enabled) {
            Log::info('2FA já habilitado, redirecionando para dashboard', ['user_id' => $user->id]);
            return redirect()->route('dashboard');
        }
        
        // Gerar segredo se não existir
        if (!$user->two_factor_secret) {
            Log::info('Gerando novo segredo 2FA', ['user_id' => $user->id]);
            $secret = $user->generateTwoFactorSecret();
            $user->update(['two_factor_secret' => $secret]);
            $user->refresh(); // Recarrega o modelo para ter o secret atualizado
        }
        
        $qrCodeSvg = $user->getTwoFactorQrCodeSvg();
        
        Log::info('Exibindo página de setup 2FA', [
            'user_id' => $user->id,
            'has_secret' => !empty($user->two_factor_secret),
            'qr_code_length' => strlen($qrCodeSvg)
        ]);
        
        return view('auth.2fa.setup', [
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $user->two_factor_secret
        ]);
    }
    
    /**
     * Habilitar 2FA após verificação
     */
    public function enable(Request $request)
    {
        $user = Auth::user();
        
        Log::info('Tentativa de habilitar 2FA', ['user_id' => $user->id, 'email' => $user->email]);
        
        $request->validate([
            'code' => 'required|string|size:6',
            'password' => 'required|string'
        ]);
        
        // Verificar senha
        if (!Hash::check($request->password, $user->password)) {
            Log::warning('Falha na verificação de senha ao habilitar 2FA', ['user_id' => $user->id]);
            throw ValidationException::withMessages([
                'password' => ['A senha fornecida está incorreta.']
            ]);
        }
        
        Log::info('Senha verificada com sucesso', ['user_id' => $user->id]);
        
        // Verificar código 2FA - CORRIGIDO: usar verifyTwoFactor ao invés de verifyTwoFactorCode
        if (!$user->verifyTwoFactor($request->code)) {
            Log::warning('Código 2FA inválido ao habilitar', ['user_id' => $user->id, 'code' => $request->code]);
            throw ValidationException::withMessages([
                'code' => ['O código fornecido é inválido.']
            ]);
        }
        
        Log::info('Código 2FA verificado com sucesso', ['user_id' => $user->id]);
        
        // Habilitar 2FA
        $user->enableTwoFactor();
        
        Log::info('2FA habilitado com sucesso', ['user_id' => $user->id]);
        
        // Marcar como verificado nesta sessão
        session(['2fa_verified' => true]);
        
        return redirect()->route('dashboard')
            ->with('success', 'Autenticação de dois fatores habilitada com sucesso!');
    }
    
    /**
     * Mostrar página de verificação do 2FA
     */
    public function showVerify()
    {
        return $this->verify();
    }
    public function verify()
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
    
    /**
     * Verificar código 2FA no login
     */
    public function check(Request $request)
    {
        $user = Auth::user();
        
        Log::info('Verificação de código 2FA iniciada', [
            'user_id' => $user->id,
            'method' => $user->two_factor_method,
            'code' => $request->code
        ]);
        
        $request->validate([
            'code' => 'required|string|size:6'
        ]);
        
        if (!$user->verifyTwoFactor($request->code)) {
            Log::warning('Código 2FA inválido no login', [
                'user_id' => $user->id,
                'code' => $request->code,
                'method' => $user->two_factor_method
            ]);
            
            // Incrementar tentativas falhadas - CORRIGIDO: usar incrementFailedAttempts
            $user->incrementFailedAttempts();
            
            throw ValidationException::withMessages([
                'code' => ['O código fornecido é inválido.']
            ]);
        }
        
        Log::info('Código 2FA verificado com sucesso no login', ['user_id' => $user->id]);
        
        // Limpar código de email se foi usado
        if ($user->two_factor_method === 'email') {
            Log::info('Limpando código de email 2FA', ['user_id' => $user->id]);
            $user->clearEmailTwoFactorCode();
        }
        
        // Resetar tentativas falhadas - CORRIGIDO: usar resetFailedAttempts
        $user->resetFailedAttempts();
        
        // Marcar como verificado nesta sessão
        session(['2fa_verified' => true]);
        
        Log::info('Login 2FA concluído com sucesso', ['user_id' => $user->id]);
        
        // REDIRECIONAMENTO BASEADO NA ROLE DO USUÁRIO
        $userRoles = $user->roles->pluck('name')->toArray();
        
        if (in_array('concessionaria', $userRoles)) {
            return redirect()->route('concessionarias.embarcacoes.index')
                ->with('success', '2FA verificado com sucesso!');
        } elseif (in_array('agente_navio', $userRoles)) {
            return redirect()->route('pedidos.index')
                ->with('success', '2FA verificado com sucesso!');
        }
        
        // Para qualquer outro role, redirecionar para o dashboard
        return redirect()->route('dashboard')->with('success', '2FA verificado com sucesso!');
    }
    
    /**
     * Desabilitar 2FA
     */
    public function disable(Request $request)
    {
        $user = Auth::user();
        
        Log::info('Tentativa de desabilitar 2FA', ['user_id' => $user->id]);
        
        $request->validate([
            'password' => 'required|string',
            'code' => 'required|string|size:6'
        ]);
        
        // Verificar senha
        if (!Hash::check($request->password, $user->password)) {
            Log::warning('Falha na verificação de senha ao desabilitar 2FA', ['user_id' => $user->id]);
            throw ValidationException::withMessages([
                'password' => ['A senha fornecida está incorreta.']
            ]);
        }
        
        Log::info('Senha verificada para desabilitar 2FA', ['user_id' => $user->id]);
        
        // Verificar código 2FA - CORRIGIDO: usar verifyTwoFactor ao invés de verifyTwoFactorCode
        if (!$user->verifyTwoFactor($request->code)) {
            Log::warning('Código 2FA inválido ao desabilitar', ['user_id' => $user->id]);
            throw ValidationException::withMessages([
                'code' => ['O código fornecido é inválido.']
            ]);
        }
        
        Log::info('Código 2FA verificado para desabilitar', ['user_id' => $user->id]);
        
        // Desabilitar 2FA
        $user->disableTwoFactor();
        
        Log::info('2FA desabilitado com sucesso', ['user_id' => $user->id]);
        
        return redirect()->route('profile.edit')
            ->with('success', 'Autenticação de dois fatores desabilitada.');
    }
    
    /**
     * Reenviar código por email
     */
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
}