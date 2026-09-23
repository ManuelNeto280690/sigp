<?php

namespace App\Helpers;

use App\Models\Configuracao;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ConfigHelper
{
    /**
     * Obter uma configuração por chave
     */
    public static function get(string $chave, $padrao = null)
    {
        try {
            $cacheKey = "config_{$chave}";
            
            return Cache::remember($cacheKey, 3600, function () use ($chave, $padrao) {
                $config = Configuracao::where('chave', $chave)
                                     ->where('is_active', true)
                                     ->first();
                
                return $config ? $config->getValorTipado() : $padrao;
            });
        } catch (\Exception $e) {
            Log::error("Erro ao obter configuração {$chave}: " . $e->getMessage());
            return $padrao;
        }
    }

    /**
     * Obter todas as configurações ativas
     */
    public static function all(): array
    {
        try {
            return Cache::remember('todas_configuracoes', 3600, function () {
                return Configuracao::where('is_active', true)
                                 ->get()
                                 ->mapWithKeys(function ($config) {
                                     return [$config->chave => $config->getValorTipado()];
                                 })
                                 ->toArray();
            });
        } catch (\Exception $e) {
            Log::error("Erro ao obter todas as configurações: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obter configurações por categoria
     */
    public static function getByCategory(string $categoria): array
    {
        try {
            $cacheKey = "configs_categoria_{$categoria}";
            
            return Cache::remember($cacheKey, 3600, function () use ($categoria) {
                return Configuracao::where('categoria', $categoria)
                                 ->where('is_active', true)
                                 ->get()
                                 ->mapWithKeys(function ($config) {
                                     return [$config->chave => $config->getValorTipado()];
                                 })
                                 ->toArray();
            });
        } catch (\Exception $e) {
            Log::error("Erro ao obter configurações da categoria {$categoria}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obter configurações públicas (para API)
     */
    public static function getPublic(): array
    {
        try {
            return Cache::remember('configuracoes_publicas', 3600, function () {
                return Configuracao::where('publico', true)
                                 ->where('is_active', true)
                                 ->get()
                                 ->mapWithKeys(function ($config) {
                                     return [$config->chave => $config->getValorTipado()];
                                 })
                                 ->toArray();
            });
        } catch (\Exception $e) {
            Log::error("Erro ao obter configurações públicas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Definir uma configuração dinamicamente
     */
    public static function set(string $chave, $valor, string $tipo = 'string'): bool
    {
        try {
            $config = Configuracao::where('chave', $chave)->first();
            
            if ($config) {
                $config->update(['valor' => $valor]);
            } else {
                Configuracao::create([
                    'chave' => $chave,
                    'valor' => $valor,
                    'tipo' => $tipo,
                    'categoria' => 'sistema',
                    'is_active' => true
                ]);
            }

            // Limpar cache
            Cache::forget("config_{$chave}");
            Cache::forget('todas_configuracoes');
            
            return true;
        } catch (\Exception $e) {
            Log::error("Erro ao definir configuração {$chave}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar se uma configuração existe
     */
    public static function exists(string $chave): bool
    {
        return Configuracao::where('chave', $chave)->exists();
    }

    /**
     * Limpar cache de configurações
     */
    public static function clearCache(): void
    {
        Cache::forget('todas_configuracoes');
        Cache::forget('configuracoes_publicas');
        
        // Limpar cache específico de cada configuração
        $chaves = Configuracao::pluck('chave');
        foreach ($chaves as $chave) {
            Cache::forget("config_{$chave}");
        }
        
        // Limpar cache por categoria
        $categorias = Configuracao::distinct('categoria')->pluck('categoria');
        foreach ($categorias as $categoria) {
            Cache::forget("configs_categoria_{$categoria}");
        }
    }

    // Métodos de conveniência para configurações específicas
    public static function nomeEmpresa(): string
    {
        return self::get('nome_empresa', 'Sistema Portuário');
    }

    public static function nomeDoSistema(): string
    {
        return self::get('nome_sistema', 'SIGP - Sistema Integrado de Gestão Portuária');
    }

    public static function logoEmpresa(): ?string
    {
        $logoPath = self::get('logo_empresa');
        
        if ($logoPath) {
            // Se já contém 'storage/', retorna com asset()
            if (strpos($logoPath, 'storage/') === 0) {
                return asset($logoPath);
            }
            // Se não contém, adiciona o prefixo storage/
            return asset('storage/' . $logoPath);
        }
        
        return null;
    }

    public static function emailSuporte(): string
    {
        return self::get('email_suporte', 'suporte@sistema.com');
    }

    public static function telefoneContato(): string
    {
        return self::get('telefone_contato', '');
    }

    public static function enderecoEmpresa(): string
    {
        return self::get('endereco_empresa', '');
    }

    public static function sistemaManutencao(): bool
    {
        return self::get('sistema_manutencao', false);
    }

    public static function mensagemManutencao(): string
    {
        return self::get('mensagem_manutencao', 'Sistema em manutenção. Tente novamente em alguns minutos.');
    }

    public static function cacheAtivo(): bool
    {
        return self::get('cache_ativo', true);
    }

    public static function tempoCacheMinutos(): int
    {
        return self::get('tempo_cache_minutos', 60);
    }

    public static function maxTentativasLogin(): int
    {
        return self::get('max_tentativas_login', 5);
    }

    public static function tempoSessaoMinutos(): int
    {
        return self::get('tempo_sessao_minutos', 120);
    }

    public static function favicon(): ?string
    {
        $faviconPath = self::get('favicon');
        
        if ($faviconPath) {
            // Se já contém 'storage/', retorna com asset()
            if (strpos($faviconPath, 'storage/') === 0) {
                return asset($faviconPath);
            }
            // Se não contém, adiciona o prefixo storage/
            return asset('storage/' . $faviconPath);
        }
        
        // Fallback para favicon padrão
        return asset('favicon.ico');
    }

    public static function emailsNotificacaoPedidos(): array
    {
        $emails = self::get('emails_notificacao_pedidos', '');
        
        if (empty($emails)) {
            return [];
        }
        
        // Separar por vírgula e limpar espaços
        $emailList = array_map('trim', explode(',', $emails));
        
        // Filtrar emails válidos
        return array_filter($emailList, function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });
    }

    public static function notificacaoEmailAtiva(): bool
    {
        return self::get('notificacao_email_ativa', true);
    }

    /**
     * Obter e-mails dos usuários com role admin
     */
    public static function emailsAdmins(): array
    {
        try {
            $admins = \App\Models\User::role('admin')
                ->pluck('email')
                ->toArray();
            
            // Filtrar emails válidos
            $validEmails = array_filter($admins, function($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            });
            
            \Illuminate\Support\Facades\Log::info('E-mails dos admins encontrados: ' . json_encode($validEmails));
            
            return $validEmails;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar e-mails dos admins: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obter e-mail do remetente
     */
    public static function emailRemetente(): string
    {
        return self::get('email_remetente', config('mail.from.address', 'uporte@sigp-angola.com'));
    }

    /**
     * Obter nome do remetente
     */
    public static function nomeRemetente(): string
    {
        return self::get('nome_remetente', config('mail.from.name', 'Sistema SIGP'));
    }

  
}