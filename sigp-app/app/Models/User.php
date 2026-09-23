<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Exception;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, HasRoles, HasPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telefone',
        'cargo',
        'departamento',
        'avatar',
        'concessionaria_id',
        'two_factor_enabled',
        'two_factor_method',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_email_code',
        'two_factor_email_code_expires_at',
        'two_factor_email_attempts',
        'two_factor_failed_attempts',
        'two_factor_locked_until',
        'two_factor_required',
        'two_factor_setup_at',
        'two_factor_setup_pending',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
        'password_changed_at',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_email_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_required' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'two_factor_email_code_expires_at' => 'datetime',
            'two_factor_locked_until' => 'datetime',
            'two_factor_setup_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
        ];
    }

    // ===== MÉTODOS BÁSICOS DE USUÁRIO =====

    /**
     * Check if user account is locked
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Lock user account
     */
    public function lockAccount(int $minutes = 15): void
    {
        $this->update([
            'locked_until' => now()->addMinutes($minutes)
        ]);
    }

    /**
     * Unlock user account
     */
    public function unlockAccount(): void
    {
        $this->update([
            'locked_until' => null,
            'failed_login_attempts' => 0
        ]);
    }

    /**
     * Increment failed login attempts
     */
    public function incrementFailedAttempts(): void
    {
        $attempts = $this->failed_login_attempts + 1;
        $updateData = ['failed_login_attempts' => $attempts];
        
        // Lock account after 5 failed attempts
        if ($attempts >= 5) {
            $updateData['locked_until'] = now()->addMinutes(15);
        }
        
        $this->update($updateData);
    }

    /**
     * Reset failed login attempts
     */
    public function resetFailedAttempts(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null
        ]);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update([
            'last_login_at' => now()
        ]);
    }

    /**
     * Check if user needs to change password
     */
    public function needsPasswordChange(): bool
    {
        if (!$this->password_changed_at) {
            return true;
        }
        
        return $this->password_changed_at->diffInDays(now()) > 90;
    }

    /**
     * Update password changed timestamp
     */
    public function updatePasswordChanged(): void
    {
        $this->update([
            'password_changed_at' => now(),
        ]);
    }

    // ===== SISTEMA 2FA UNIFICADO =====

    /**
     * Get available two factor authentication methods
     */
    public static function getTwoFactorMethods(): array
    {
        return [
            'email' => 'Email',
            'authenticator' => 'Authenticator App (Google Authenticator, Authy, etc.)'
        ];
    }

    /**
     * Verifica se o usuário precisa configurar 2FA pela primeira vez
     */
    public function needs2FASetup(): bool
    {
        // Se tem setup pendente, precisa configurar
        if ($this->two_factor_setup_pending) {
            return true;
        }
        
        // Se 2FA não está habilitado, não precisa setup
        if (!$this->two_factor_enabled) {
            return false;
        }
        
        // Se método é email, não precisa setup adicional
        if ($this->two_factor_method === 'email') {
            return false;
        }
        
        // Se método é authenticator, verifica se tem segredo E se foi configurado
        if ($this->two_factor_method === 'authenticator') {
            return empty($this->two_factor_secret) || empty($this->two_factor_setup_at);
        }
        
        // Por padrão, não precisa setup
        return false;
    }

    /**
     * Marca o 2FA como pendente de configuração
     */
    public function markTwoFactorSetupPending(string $method): void
    {
        $this->update([
            'two_factor_method' => $method,
            'two_factor_setup_pending' => true,
            'two_factor_enabled' => false, // Não ativar ainda
        ]);
    }

    /**
     * Completa a configuração do 2FA
     */
    public function completeTwoFactorSetup(): void
    {
        $this->update([
            'two_factor_enabled' => true,
            'two_factor_setup_pending' => false,
            'two_factor_setup_at' => now(),
        ]);
    }

    /**
     * Verifica se o 2FA é opcional para este usuário
     */
    public function is2FAOptional(): bool
    {
        // 2FA é sempre opcional no novo sistema
        return true;
    }

    /**
     * Marca o 2FA como obrigatório para este usuário (usado pelo admin)
     */
    public function require2FA(): void
    {
        $this->update(['two_factor_required' => true]);
    }

    /**
     * Remove a obrigatoriedade do 2FA para este usuário
     */
    public function makeOptional2FA(): void
    {
        $this->update(['two_factor_required' => false]);
    }

    /**
     * Configura o 2FA pela primeira vez
     */
    public function setup2FA(string $method, array $data = []): bool
    {
        try {
            $updateData = [
                'two_factor_method' => $method,
                'two_factor_enabled' => true,
                'two_factor_setup_at' => now(),
            ];
            
            if ($method === 'authenticator') {
                $updateData['two_factor_secret'] = $data['secret'] ?? $this->generateTwoFactorSecret();
                $updateData['two_factor_recovery_codes'] = $data['recovery_codes'] ?? $this->generateRecoveryCodes();
            }
            
            $this->update($updateData);
            
            Log::info('2FA configurado com sucesso', [
                'user_id' => $this->id,
                'method' => $method
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error('Erro ao configurar 2FA', [
                'user_id' => $this->id,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Desabilita o 2FA completamente
     */
    public function disable2FA(): bool
    {
        try {
            $this->update([
                'two_factor_enabled' => false,
                'two_factor_method' => null,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_email_code' => null,
                'two_factor_email_code_expires_at' => null,
                'two_factor_email_attempts' => 0,
                'two_factor_failed_attempts' => 0,
                'two_factor_locked_until' => null,
            ]);
            
            Log::info('2FA desabilitado', ['user_id' => $this->id]);
            return true;
        } catch (Exception $e) {
            Log::error('Erro ao desabilitar 2FA', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verifica se o usuário está bloqueado por tentativas de 2FA
     */
    public function is2FALocked(): bool
    {
        return $this->two_factor_locked_until && $this->two_factor_locked_until->isFuture();
    }

    /**
     * Incrementa tentativas falhadas de 2FA e bloqueia se necessário
     */
    public function increment2FAFailedAttempts(): void
    {
        $attempts = $this->two_factor_failed_attempts + 1;
        $updateData = ['two_factor_failed_attempts' => $attempts];
        
        // Bloqueia por 15 minutos após 5 tentativas
        if ($attempts >= 5) {
            $updateData['two_factor_locked_until'] = now()->addMinutes(15);
            Log::warning('Usuário bloqueado por tentativas 2FA', [
                'user_id' => $this->id,
                'attempts' => $attempts
            ]);
        }
        
        $this->update($updateData);
    }

    /**
     * Reseta tentativas falhadas de 2FA
     */
    public function reset2FAFailedAttempts(): void
    {
        $this->update([
            'two_factor_failed_attempts' => 0,
            'two_factor_locked_until' => null,
        ]);
    }

    // ===== MÉTODOS PARA 2FA POR EMAIL =====

    /**
     * Generate email two factor code
     */
    public function generateEmailTwoFactorCode(): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->update([
            'two_factor_email_code' => Hash::make($code),
            'two_factor_email_code_expires_at' => now()->addMinutes(10),
            'two_factor_email_attempts' => 0
        ]);
        
        Log::info('Código 2FA por email gerado', ['user_id' => $this->id]);
        
        return $code;
    }

    /**
     * Envia código 2FA por email
     */
    public function sendTwoFactorEmailCode(): bool
    {
        try {
            $code = $this->generateEmailTwoFactorCode();
            
            Mail::to($this->email)->send(new \App\Mail\TwoFactorCodeMail($code, $this->name));
            
            Log::info('Código 2FA por email enviado', ['user_id' => $this->id]);
            return true;
        } catch (Exception $e) {
            Log::error('Erro ao enviar código 2FA por email', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verify email two factor code with proper expiration check
     */
    public function verifyEmailTwoFactorCode(string $code): bool
    {
        if (!$this->two_factor_email_code || !$this->two_factor_email_code_expires_at) {
            Log::warning('Código 2FA por email não encontrado', ['user_id' => $this->id]);
            return false;
        }
        
        // Verifica se está bloqueado
        if ($this->is2FALocked()) {
            return false;
        }
        
        // Verifica se o código expirou
        if (now()->isAfter($this->two_factor_email_code_expires_at)) {
            Log::warning('Código 2FA por email expirado', ['user_id' => $this->id]);
            $this->clearEmailTwoFactorCode();
            return false;
        }
        
        // Verifica o código
        if (!Hash::check($code, $this->two_factor_email_code)) {
            $this->increment2FAFailedAttempts();
            Log::warning('Código 2FA por email inválido', [
                'user_id' => $this->id,
                'attempts' => $this->two_factor_email_attempts
            ]);
            return false;
        }
        
        // Código válido - limpa e reseta tentativas
        $this->clearEmailTwoFactorCode();
        $this->reset2FAFailedAttempts();
        
        Log::info('Código 2FA por email verificado com sucesso', ['user_id' => $this->id]);
        return true;
    }

    /**
     * Clear email two factor code
     */
    public function clearEmailTwoFactorCode(): void
    {
        $this->update([
            'two_factor_email_code' => null,
            'two_factor_email_code_expires_at' => null,
            'two_factor_email_attempts' => 0,
        ]);
        
        Log::info('Código 2FA por email limpo', ['user_id' => $this->id]);
    }

    // ===== MÉTODOS PARA AUTHENTICATOR =====

    /**
     * Verifica código do authenticator
     */
    public function verifyAuthenticatorTwoFactorCode(string $code): bool
    {
        if ($this->is2FALocked()) {
            return false;
        }
        
        if (!$this->two_factor_secret) {
            return false;
        }
        
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($this->two_factor_secret, $code);
        
        if ($valid) {
            $this->reset2FAFailedAttempts();
            Log::info('Código authenticator verificado com sucesso', ['user_id' => $this->id]);
            return true;
        }
        
        $this->increment2FAFailedAttempts();
        return false;
    }

    /**
     * Gera secret para authenticator
     */
    public function generateTwoFactorSecret(): string
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        
        // Salvar o secret no banco de dados
        $this->update(['two_factor_secret' => $secret]);
        
        return $secret;
    }

    /**
     * Gera códigos de recuperação
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(10);
        }
        return $codes;
    }

    /**
     * Obtém URL do QR Code para authenticator
     */
    public function getTwoFactorQrCodeUrl(): string
    {
        if (!$this->two_factor_secret) {
            return '';
        }
        
        $appName = config('app.name');
        return "otpauth://totp/{$appName}:{$this->email}?secret={$this->two_factor_secret}&issuer={$appName}";
    }

    /**
     * Get QR Code as SVG
     */
    public function getTwoFactorQrCodeSvg(): string
    {
        $url = $this->getTwoFactorQrCodeUrl();
        
        Log::info('Gerando QR Code', [
            'user_id' => $this->id,
            'url' => $url,
            'secret' => $this->two_factor_secret
        ]);
        
        if (empty($url)) {
            Log::warning('URL do QR Code vazia', ['user_id' => $this->id]);
            return '';
        }
        
        try {
            // Verificar se as classes necessárias existem
            if (!class_exists('BaconQrCode\\Renderer\\ImageRenderer')) {
                throw new Exception('Classe ImageRenderer não encontrada. Execute: composer require bacon/bacon-qr-code');
            }
            
            $renderer = new ImageRenderer(
                new RendererStyle(200),
                new SvgImageBackEnd()
            );
            
            $writer = new Writer($renderer);
            $svg = $writer->writeString($url);
            
            Log::info('QR Code gerado com sucesso', [
                'user_id' => $this->id,
                'svg_length' => strlen($svg)
            ]);
            
            return $svg;
        } catch (Exception $e) {
            Log::error('Erro ao gerar QR Code', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return '';
        }
    }

    // ===== MÉTODO UNIFICADO DE VERIFICAÇÃO =====

    /**
     * Verifica código 2FA independente do método
     */
    public function verifyTwoFactor(string $code): bool
    {
        if (!$this->two_factor_enabled) {
            return true; // Se não tem 2FA habilitado, passa
        }
        
        switch ($this->two_factor_method) {
            case 'email':
                return $this->verifyEmailTwoFactorCode($code);
            case 'authenticator':
                return $this->verifyAuthenticatorTwoFactorCode($code);
            default:
                Log::warning('Método 2FA desconhecido', [
                    'user_id' => $this->id,
                    'method' => $this->two_factor_method
                ]);
                return false;
        }
    }

    // ===== RELACIONAMENTOS =====

    /**
     * Get user sessions
     */
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Get the concessionaria that owns the user
     */
    public function concessionaria()
    {
        return $this->belongsTo(Concessionaria::class, 'concessionaria_id');
    }
}
