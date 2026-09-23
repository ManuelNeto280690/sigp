<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorAuthController extends Controller
{
    protected $google2fa;
    
    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }
    
    /**
     * Mostra a página de setup inicial do 2FA
     */
    public function showSetup()
    {
        $user = Auth::user();
        
        // Se já tem 2FA configurado, redireciona para verificação
        if ($user->two_factor_enabled) {
            return redirect()->route('2fa.verify');
        }
        
        // Gerar segredo se não existir
        if (!$user->two_factor_secret) {
            $secret = $this->google2fa->generateSecretKey();
            $user->update(['two_factor_secret' => $secret]);
            $user->refresh();
        }
        
        $qrCodeSvg = $this->generateQrCodeSvg($user);
        
        return view('auth.2fa.setup', [
            'user' => $user,
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $user->two_factor_secret
        ]);
    }
    
    /**
     * Configura o 2FA pela primeira vez (obrigatório ou opcional)
     */
    public function setup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'method' => 'required|in:email,authenticator',
            'email' => 'required_if:method,email|email',
            'authenticator_code' => 'required_if:method,authenticator|string|size:6'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $user = Auth::user();
        
        // Verifica a senha atual
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Senha atual incorreta.'
            ]);
        }
        
        try {
            Log::info('DEBUG: Método recebido', [
                'method' => $request->method,
                'method_type' => gettype($request->method),
                'is_email' => $request->method === 'email',
                'is_authenticator' => $request->method === 'authenticator'
            ]);
            
            if ($request->method === 'email') {
                Log::info('DEBUG: Executando setup EMAIL');
                $this->setupEmailTwoFactor($user, $request->email);
            } else {
                Log::info('DEBUG: Executando setup AUTHENTICATOR');
                $this->setupAuthenticatorTwoFactor($user, $request->authenticator_code);
                
                // CORREÇÃO: Marcar como verificado ANTES de qualquer redirecionamento
                $currentSessionId = session()->getId();
                
                // Atualizar o current_session_id do usuário ANTES de marcar 2FA
                $user->update(['current_session_id' => $currentSessionId]);
                
                // Agora marcar a sessão como verificada
                session()->put('2fa_verified', true);
                session()->put('2fa_verified_at', now()->toISOString());
                
                // Força salvamento da sessão
                session()->save();
                
                Log::info('Sessão 2FA marcada como verificada após configuração do autenticador', [
                    'user_id' => $user->id,
                    'session_2fa_verified' => session('2fa_verified'),
                    'session_id' => $currentSessionId,
                    'user_current_session_id' => $user->current_session_id
                ]);
            }
            
            Log::info('2FA configurado com sucesso', [
                'user_id' => $user->id,
                'method' => $request->method,
                'ip' => $request->ip()
            ]);
            
            // REDIRECIONAMENTO BASEADO NA ROLE DO USUÁRIO APÓS CONFIGURAÇÃO
            $userRoles = $user->roles->pluck('name')->toArray();
            
            if (in_array('concessionaria', $userRoles)) {
                return redirect()->route('concessionarias.embarcacoes.index')
                    ->with('success', 'Autenticação de dois fatores configurada com sucesso!');
            } elseif (in_array('agente_navio', $userRoles)) {
                return redirect()->route('pedidos.index')
                    ->with('success', 'Autenticação de dois fatores configurada com sucesso!');
            }
            
            return redirect()->route('dashboard')
                ->with('success', 'Autenticação de dois fatores configurada com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro ao configurar 2FA', [
                'user_id' => $user->id,
                'method' => $request->method,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Erro ao configurar 2FA: ' . $e->getMessage());
        }
    }
    
    /**
     * Mostra a página de verificação do 2FA
     */
    public function showVerify()
    {
        $user = Auth::user();
        
        if (!$user->two_factor_enabled) {
            return redirect()->route('dashboard');
        }
        
        // Enviar código por email apenas se não existir um válido
        if ($user->two_factor_method === 'email') {
            $hasValid = $user->two_factor_email_code && $user->two_factor_email_code_expires_at && now()->isBefore($user->two_factor_email_code_expires_at);
            if (!$hasValid) {
                try {
                    $result = $user->sendTwoFactorEmailCode();
                    if (!$result) {
                        Log::error('Falha ao enviar código 2FA por email', ['user_id' => $user->id, 'email' => $user->email]);
                        return back()->with('error', 'Erro ao enviar código por email. Verifique sua configuração de email.');
                    }
                    Log::info('Código 2FA enviado com sucesso', ['user_id' => $user->id, 'method' => 'email']);
                } catch (\Exception $e) {
                    Log::error('Exceção ao enviar código 2FA por email', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                    return back()->with('error', 'Erro interno ao enviar email: ' . $e->getMessage());
                }
            } else {
                Log::info('Código 2FA existente e válido, não reenviando', ['user_id' => $user->id]);
            }
        }
        
        return view('auth.2fa.verify', compact('user'));
    }
    
    /**
     * Verifica o código 2FA
     */
    public function verify(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string|size:6'
            ]);
            
            $user = Auth::user();
            
            if (!$user->two_factor_enabled) {
                return redirect()->route('dashboard');
            }
            
            Log::info('Iniciando verificação 2FA', [
                'user_id' => $user->id,
                'method' => $user->two_factor_method,
                'code' => $request->code
            ]);
            
            // CORREÇÃO: Usar o método unificado do modelo User
            $isValid = $user->verifyTwoFactor($request->code);
            
            Log::info('Resultado verificação 2FA', [
                'user_id' => $user->id, 
                'valid' => $isValid,
                'method' => $user->two_factor_method
            ]);
            
            if ($isValid) {
                Log::info('CÓDIGO VÁLIDO - Definindo sessão', [
                    'user_id' => $user->id,
                    'method' => $user->two_factor_method
                ]);
                
                // Limpa tentativas falhadas (já feito no modelo User)
                // Marca como verificado na sessão por 24 horas
                session([
                    '2fa_verified' => true,
                    '2fa_verified_at' => now()->toISOString()
                ]);
                
                // Limpa TODAS as URLs problemáticas da sessão
                session()->forget(['_previous', 'url.intended']);
                
                // Força salvar a sessão ANTES do redirecionamento
                session()->save();
                
                Log::info('Sessão 2FA configurada e salva', [
                    'user_id' => $user->id,
                    'session_2fa_verified' => session('2fa_verified'),
                    'session_id' => session()->getId()
                ]);
                
                Log::info('2FA verificado com sucesso', [
                    'user_id' => $user->id,
                    'method' => $user->two_factor_method,
                    'ip' => $request->ip()
                ]);
                
                // REDIRECIONAMENTO BASEADO NA ROLE DO USUÁRIO
                $userRoles = $user->roles->pluck('name')->toArray();
                
                if (in_array('concessionaria', $userRoles)) {
                    return redirect()->route('concessionarias.embarcacoes.index')
                        ->with('success', 'Autenticação verificada com sucesso!');
                } elseif (in_array('agente_navio', $userRoles)) {
                    return redirect()->route('pedidos.index')
                        ->with('success', 'Autenticação verificada com sucesso!');
                }
                
                // Para qualquer outro role, redirecionar para o dashboard
                return redirect()->route('dashboard')->with('success', 'Autenticação verificada com sucesso!');
                
            } else {
                Log::warning('CÓDIGO INVÁLIDO - Detalhes', [
                    'user_id' => $user->id,
                    'method' => $user->two_factor_method,
                    'code' => $request->code,
                    'has_secret' => !empty($user->two_factor_secret)
                ]);
                
                // As tentativas falhadas já são gerenciadas no modelo User
                
                Log::warning('Tentativa de 2FA falhada', [
                    'user_id' => $user->id,
                    'method' => $user->two_factor_method,
                    'attempts' => $user->two_factor_failed_attempts,
                    'ip' => $request->ip()
                ]);
                
                // Verificar se está bloqueado
                if ($user->is2FALocked()) {
                    Auth::logout();
                    return redirect()->route('login')
                        ->with('error', 'Muitas tentativas falhadas. Tente novamente em 15 minutos.');
                }
                
                return back()->withErrors(['code' => 'Código inválido. Tente novamente.']);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro na verificação 2FA', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Erro interno. Tente novamente.');
        }
    }
    
    /**
     * Reenvia código por email
     */
    public function resendEmailCode(Request $request)
    {
        $user = Auth::user();
        
        if ($user->two_factor_method !== 'email') {
            return response()->json(['error' => 'Método 2FA não é email'], 400);
        }
        
        // Não gerar novo código se já existir um válido (evita loop de geração)
        $hasValid = $user->two_factor_email_code && $user->two_factor_email_code_expires_at && now()->isBefore($user->two_factor_email_code_expires_at);
        if ($hasValid) {
            $seconds = now()->diffInSeconds($user->two_factor_email_code_expires_at);
            return response()->json(['message' => "Código já enviado. Aguarde {$seconds}s."], 200);
        }
        try {
            $result = $user->sendTwoFactorEmailCode();
            if (!$result) {
                Log::error('Falha ao reenviar código 2FA por email', ['user_id' => $user->id, 'email' => $user->email]);
                return response()->json(['error' => 'Erro ao enviar código por email'], 500);
            }
            Log::info('Código 2FA reenviado com sucesso', ['user_id' => $user->id, 'ip' => $request->ip()]);
            return response()->json(['message' => 'Código reenviado com sucesso!']);
        } catch (\Exception $e) {
            Log::error('Exceção ao reenviar código 2FA por email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Desabilita o 2FA
     */
    public function disable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }
        
        $user = Auth::user();
        
        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Senha incorreta.');
        }
        
        try {
            $user->update([
                'two_factor_enabled' => false,
                'two_factor_method' => null,
                'two_factor_email' => null,
                'two_factor_email_code' => null,
                'two_factor_email_code_expires_at' => null,
                'two_factor_authenticator_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_failed_attempts' => 0
            ]);
            
            // Remove verificação da sessão
            session()->forget(['2fa_verified', '2fa_verified_at']);
            
            Log::info('2FA desabilitado', [
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);
            
            return back()->with('success', 'Autenticação de dois fatores desabilitada.');
            
        } catch (\Exception $e) {
            Log::error('Erro ao desabilitar 2FA', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Erro ao desabilitar 2FA.');
        }
    }

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
     * Configura 2FA por email
     */
    private function setupEmailTwoFactor($user, $email)
    {
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_method' => 'email',
            'two_factor_setup_at' => now(),
            'two_factor_failed_attempts' => 0
        ]);
        
        // Gera códigos de recuperação
        $recoveryCodes = $this->generateRecoveryCodes();
        $user->update(['two_factor_recovery_codes' => json_encode($recoveryCodes)]);
    }
    
    /**
     * Configura 2FA por authenticator
     */
    private function setupAuthenticatorTwoFactor($user, $code)
    {
        // Gerar segredo se não existir
        if (!$user->two_factor_secret) {
            $secret = $this->google2fa->generateSecretKey();
            $user->update(['two_factor_secret' => $secret]);
            $user->refresh();
        }
        
        // Verificar o código fornecido
        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $code);
        
        if (!$valid) {
            throw new \Exception('Código de verificação inválido.');
        }
        
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_method' => 'authenticator',
            'two_factor_authenticator_secret' => $user->two_factor_secret,
            'two_factor_setup_at' => now(),
            'two_factor_failed_attempts' => 0
        ]);
        
        // Gera códigos de recuperação
        $recoveryCodes = $this->generateRecoveryCodes();
        $user->update(['two_factor_recovery_codes' => json_encode($recoveryCodes)]);
    }
    
    /**
     * Verifica código do authenticator
     */
    private function verifyAuthenticatorCode($user, $code)
    {
        Log::info('Debug verifyAuthenticatorCode - INÍCIO', [
            'user_id' => $user->id,
            'code' => $code,
            'has_secret' => !empty($user->two_factor_secret),
            'secret_length' => $user->two_factor_secret ? strlen($user->two_factor_secret) : 0,
            'secret_preview' => $user->two_factor_secret ? substr($user->two_factor_secret, 0, 4) . '...' : null,
            'method' => $user->two_factor_method
        ]);
        
        if (!$user->two_factor_secret) {
            Log::warning('ERRO: Usuário sem two_factor_secret', [
                'user_id' => $user->id,
                'method' => $user->two_factor_method
            ]);
            return false;
        }
        
        try {
            // CORREÇÃO: Usar o método do modelo User que já está funcionando
            return $user->verifyTwoFactor($code);
        } catch (\Exception $e) {
            Log::error('ERRO no verifyTwoFactor', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'code' => $code,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Gera QR Code SVG
     */
    private function generateQrCodeSvg($user)
    {
        if (!$user->two_factor_secret) {
            return '';
        }
        
        try {
            $appName = config('app.name', 'SIGP');
            $url = "otpauth://totp/{$appName}:{$user->email}?secret={$user->two_factor_secret}&issuer={$appName}";
            
            $renderer = new ImageRenderer(
                new RendererStyle(200),
                new SvgImageBackEnd()
            );
            
            $writer = new Writer($renderer);
            return $writer->writeString($url);
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar QR Code', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }
    

    

    
    /**
     * Gera códigos de recuperação
     */
    private function generateRecoveryCodes()
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }
}