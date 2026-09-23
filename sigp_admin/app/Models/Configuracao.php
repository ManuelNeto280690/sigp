<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class Configuracao extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'configuracoes';

    protected $fillable = [
        'chave',
        'valor',
        'logo',
        'tipo',
        'descricao',
        'categoria',
        'editavel',
        'is_active'
    ];

    protected $casts = [
        'editavel' => 'boolean',
        'is_active' => 'boolean'
    ];

    protected $attributes = [
        'tipo' => 'string',
        'categoria' => 'geral',
        'editavel' => true,
        'is_active' => true
    ];

    // Scopes
    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeEditaveis($query)
    {
        return $query->where('editavel', true);
    }

    public function scopeNaoEditaveis($query)
    {
        return $query->where('editavel', false);
    }

    public function scopeAtivas($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGerais($query)
    {
        return $query->where('categoria', 'geral');
    }

    public function scopeSistema($query)
    {
        return $query->where('categoria', 'sistema');
    }

    public function scopeSeguranca($query)
    {
        return $query->where('categoria', 'seguranca');
    }

    public function scopeEmail($query)
    {
        return $query->where('categoria', 'email');
    }

    public function scopeNotificacoes($query)
    {
        return $query->where('categoria', 'notificacoes');
    }

    public function scopeRelatorios($query)
    {
        return $query->where('categoria', 'relatorios');
    }

    // Métodos auxiliares
    public function isEditavel(): bool
    {
        return $this->editavel;
    }

    public function isString(): bool
    {
        return $this->tipo === 'string';
    }

    public function isInteger(): bool
    {
        return $this->tipo === 'integer';
    }

    public function isBoolean(): bool
    {
        return $this->tipo === 'boolean';
    }

    public function isJson(): bool
    {
        return $this->tipo === 'json';
    }

    public function isFloat(): bool
    {
        return $this->tipo === 'float';
    }

    public function isDate(): bool
    {
        return $this->tipo === 'date';
    }

    // Métodos para obter valor tipado
    /**
     * Obter valor tipado da configuração
     */
    public function getValorTipado()
    {
        switch ($this->tipo) {
            case 'boolean':
                return filter_var($this->valor, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $this->valor;
            case 'float':
                return (float) $this->valor;
            case 'json':
                return json_decode($this->valor, true);
            case 'date':
                return \Carbon\Carbon::parse($this->valor);
            case 'array':
                return explode(',', $this->valor);
            default:
                return $this->valor;
        }
    }

    public function setValorTipado($valor): void
    {
        $this->valor = match($this->tipo) {
            'json' => json_encode($valor),
            'date' => $valor instanceof \Carbon\Carbon ? $valor->toDateString() : $valor,
            'boolean' => $valor ? '1' : '0',
            default => (string) $valor
        };
    }

    // Métodos estáticos para configurações
    public static function obter(string $chave, $padrao = null)
    {
        $cacheKey = "config_{$chave}";
        
        return Cache::remember($cacheKey, 3600, function () use ($chave, $padrao) {
            $config = self::where('chave', $chave)
                         ->where('is_active', true)
                         ->first();
            
            return $config ? $config->getValorTipado() : $padrao;
        });
    }

    public static function definir(string $chave, $valor, string $tipo = 'string', string $categoria = 'geral', string $descricao = null): self
    {
        $config = self::firstOrNew(['chave' => $chave]);
        
        $config->tipo = $tipo;
        $config->categoria = $categoria;
        $config->descricao = $descricao;
        $config->setValorTipado($valor);
        $config->save();
        
        // Limpar cache
        Cache::forget("config_{$chave}");
        
        return $config;
    }

    public static function remover(string $chave): bool
    {
        $config = self::where('chave', $chave)->first();
        
        if ($config && $config->isEditavel()) {
            $config->delete();
            Cache::forget("config_{$chave}");
            return true;
        }
        
        return false;
    }

    public static function existe(string $chave): bool
    {
        return self::where('chave', $chave)->where('is_active', true)->exists();
    }

    public static function porCategoria(string $categoria): array
    {
        $cacheKey = "configs_categoria_{$categoria}";
        
        return Cache::remember($cacheKey, 3600, function () use ($categoria) {
            return self::where('categoria', $categoria)
                      ->where('is_active', true)
                      ->get()
                      ->mapWithKeys(function ($config) {
                          return [$config->chave => $config->getValorTipado()];
                      })
                      ->toArray();
        });
    }

    public static function todasConfiguracoes(): array
    {
        $cacheKey = 'todas_configuracoes';
        
        return Cache::remember($cacheKey, 3600, function () {
            return self::where('is_active', true)
                      ->get()
                      ->mapWithKeys(function ($config) {
                          return [$config->chave => $config->getValorTipado()];
                      })
                      ->toArray();
        });
    }

    // Configurações específicas do sistema
    public static function nomeDoSistema(): string
    {
        return self::obter('nome_sistema', 'SIGP - Sistema Integrado de Gestão Portuária');
    }

    public static function nomeEmpresa(): string
    {
        return self::obter('nome_empresa', 'Porto de Soyo');
    }

    public static function versaoDoSistema(): string
    {
        return self::obter('versao_sistema', '1.0.0');
    }

    public static function logoDoSistema(): ?string
    {
        return self::obter('logo_sistema');
    }

    public static function emailContato(): string
    {
        return self::obter('email_contato', 'contato@sigp.com.br');
    }

    public static function telefoneContato(): ?string
    {
        return self::obter('telefone_contato');
    }

    public static function enderecoCompleto(): ?string
    {
        return self::obter('endereco_completo');
    }

    public static function fusoHorario(): string
    {
        return self::obter('fuso_horario', 'America/Sao_Paulo');
    }

    public static function idiomaPadrao(): string
    {
        return self::obter('idioma_padrao', 'pt_BR');
    }

    public static function moedaPadrao(): string
    {
        return self::obter('moeda_padrao', 'BRL');
    }

    // Configurações de segurança
    public static function tentativasMaximasLogin(): int
    {
        return self::obter('tentativas_maximas_login', 5);
    }

    public static function tempoExpiracaoSessao(): int
    {
        return self::obter('tempo_expiracao_sessao', 120); // minutos
    }

    public static function forcaMinimaSenha(): int
    {
        return self::obter('forca_minima_senha', 8);
    }

    public static function require2FA(): bool
    {
        return self::obter('require_2fa', true);
    }

    public static function logAuditoria(): bool
    {
        return self::obter('log_auditoria', true);
    }

    // Configurações de email
    public static function emailRemetente(): string
    {
        return self::obter('email_remetente', 'noreply@sigp.com.br');
    }

    public static function nomeRemetente(): string
    {
        return self::obter('nome_remetente', 'SIGP Sistema');
    }

    public static function smtpHost(): string
    {
        return self::obter('smtp_host', 'localhost');
    }

    public static function smtpPorta(): int
    {
        return self::obter('smtp_porta', 587);
    }

    public static function smtpUsuario(): ?string
    {
        return self::obter('smtp_usuario');
    }

    public static function smtpSenha(): ?string
    {
        return self::obter('smtp_senha');
    }

    public static function smtpCriptografia(): string
    {
        return self::obter('smtp_criptografia', 'tls');
    }

    // Configurações de notificações
    public static function notificacoesEmail(): bool
    {
        return self::obter('notificacoes_email', true);
    }

    public static function notificacoesSms(): bool
    {
        return self::obter('notificacoes_sms', false);
    }

    public static function notificacoesPush(): bool
    {
        return self::obter('notificacoes_push', true);
    }

    // Configurações de relatórios
    public static function formatoPadraoRelatorio(): string
    {
        return self::obter('formato_padrao_relatorio', 'pdf');
    }

    public static function logoRelatorios(): ?string
    {
        return self::obter('logo_relatorios');
    }

    public static function rodapeRelatorios(): ?string
    {
        return self::obter('rodape_relatorios');
    }

    // Configurações de backup
    public static function backupAutomatico(): bool
    {
        return self::obter('backup_automatico', true);
    }

    public static function frequenciaBackup(): string
    {
        return self::obter('frequencia_backup', 'diario'); // diario, semanal, mensal
    }

    public static function manterBackupsPor(): int
    {
        return self::obter('manter_backups_por', 30); // dias
    }

    // Configurações de manutenção
    public static function modoManutencao(): bool
    {
        return self::obter('modo_manutencao', false);
    }

    public static function mensagemManutencao(): string
    {
        return self::obter('mensagem_manutencao', 'Sistema em manutenção. Tente novamente em alguns minutos.');
    }

    // Configurações de performance
    public static function cacheAtivo(): bool
    {
        return self::obter('cache_ativo', true);
    }

    public static function tempoCacheMinutos(): int
    {
        return self::obter('tempo_cache_minutos', 60);
    }

    public static function compressaoAtiva(): bool
    {
        return self::obter('compressao_ativa', true);
    }

    // Métodos para limpar cache
    public static function limparCache(): void
    {
        Cache::flush();
    }

    public static function limparCacheCategoria(string $categoria): void
    {
        Cache::forget("configs_categoria_{$categoria}");
        Cache::forget('todas_configuracoes');
    }

    // Classes CSS para badges
    public function getTipoBadgeClass(): string
    {
        return match($this->tipo) {
            'string' => 'bg-blue-100 text-blue-800',
            'integer' => 'bg-green-100 text-green-800',
            'float' => 'bg-green-100 text-green-800',
            'boolean' => 'bg-purple-100 text-purple-800',
            'json' => 'bg-orange-100 text-orange-800',
            'date' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getCategoriaBadgeClass(): string
    {
        return match($this->categoria) {
            'geral' => 'bg-blue-100 text-blue-800',
            'sistema' => 'bg-green-100 text-green-800',
            'seguranca' => 'bg-red-100 text-red-800',
            'email' => 'bg-yellow-100 text-yellow-800',
            'notificacoes' => 'bg-purple-100 text-purple-800',
            'relatorios' => 'bg-indigo-100 text-indigo-800',
            'backup' => 'bg-orange-100 text-orange-800',
            'performance' => 'bg-pink-100 text-pink-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    // Eventos do modelo
    protected static function boot()
    {
        parent::boot();
        
        // Limpar cache quando configuração for alterada
        static::saved(function ($config) {
            \App\Helpers\ConfigHelper::clearCache();
        });
        
        static::deleted(function ($config) {
            \App\Helpers\ConfigHelper::clearCache();
        });
    }

    // Validações
    public static function validationRules($id = null): array
    {
        return [
            'chave' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('configuracoes')->ignore($id)
            ],
            'valor' => 'nullable|string',
            'logo' => 'nullable|string',
            'tipo' => 'required|in:string,integer,float,boolean,json,date',
            'descricao' => 'nullable|string',
            'categoria' => 'required|string|max:255',
            'editavel' => 'boolean',
            'is_active' => 'boolean'
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'chave.required' => 'A chave é obrigatória.',
            'chave.unique' => 'Esta chave já está em uso.',
            'chave.regex' => 'A chave deve conter apenas letras minúsculas, números e underscores.',
            'tipo.required' => 'O tipo é obrigatório.',
            'tipo.in' => 'Tipo inválido.',
            'categoria.required' => 'A categoria é obrigatória.'
        ];
    }

    // Método para inicializar configurações padrão
    public static function inicializarConfiguracoesDefault(): void
    {
        $configuracoes = [
            // Configurações gerais
            ['chave' => 'nome_sistema', 'valor' => 'SIGP - Sistema Integrado de Gestão Portuária', 'tipo' => 'string', 'categoria' => 'geral', 'descricao' => 'Nome do sistema'],
            ['chave' => 'versao_sistema', 'valor' => '1.0.0', 'tipo' => 'string', 'categoria' => 'geral', 'descricao' => 'Versão do sistema'],
            ['chave' => 'email_contato', 'valor' => 'contato@sigp.com.br', 'tipo' => 'string', 'categoria' => 'geral', 'descricao' => 'Email de contato'],
            ['chave' => 'fuso_horario', 'valor' => 'America/Sao_Paulo', 'tipo' => 'string', 'categoria' => 'geral', 'descricao' => 'Fuso horário padrão'],
            ['chave' => 'idioma_padrao', 'valor' => 'pt_BR', 'tipo' => 'string', 'categoria' => 'geral', 'descricao' => 'Idioma padrão'],
            ['chave' => 'moeda_padrao', 'valor' => 'BRL', 'tipo' => 'string', 'categoria' => 'geral', 'descricao' => 'Moeda padrão'],
            
            // Configurações de segurança
            ['chave' => 'tentativas_maximas_login', 'valor' => '5', 'tipo' => 'integer', 'categoria' => 'seguranca', 'descricao' => 'Número máximo de tentativas de login'],
            ['chave' => 'tempo_expiracao_sessao', 'valor' => '120', 'tipo' => 'integer', 'categoria' => 'seguranca', 'descricao' => 'Tempo de expiração da sessão em minutos'],
            ['chave' => 'forca_minima_senha', 'valor' => '8', 'tipo' => 'integer', 'categoria' => 'seguranca', 'descricao' => 'Tamanho mínimo da senha'],
            ['chave' => 'require_2fa', 'valor' => '1', 'tipo' => 'boolean', 'categoria' => 'seguranca', 'descricao' => 'Exigir autenticação de dois fatores'],
            ['chave' => 'log_auditoria', 'valor' => '1', 'tipo' => 'boolean', 'categoria' => 'seguranca', 'descricao' => 'Ativar log de auditoria'],
            
            // Configurações de email
            ['chave' => 'email_remetente', 'valor' => 'noreply@sigp.com.br', 'tipo' => 'string', 'categoria' => 'email', 'descricao' => 'Email remetente padrão'],
            ['chave' => 'nome_remetente', 'valor' => 'SIGP Sistema', 'tipo' => 'string', 'categoria' => 'email', 'descricao' => 'Nome do remetente padrão'],
            ['chave' => 'smtp_host', 'valor' => 'localhost', 'tipo' => 'string', 'categoria' => 'email', 'descricao' => 'Servidor SMTP'],
            ['chave' => 'smtp_porta', 'valor' => '587', 'tipo' => 'integer', 'categoria' => 'email', 'descricao' => 'Porta SMTP'],
            ['chave' => 'smtp_criptografia', 'valor' => 'tls', 'tipo' => 'string', 'categoria' => 'email', 'descricao' => 'Tipo de criptografia SMTP'],
            
            // Configurações de notificações
            ['chave' => 'notificacoes_email', 'valor' => '1', 'tipo' => 'boolean', 'categoria' => 'notificacoes', 'descricao' => 'Ativar notificações por email'],
            ['chave' => 'notificacoes_sms', 'valor' => '0', 'tipo' => 'boolean', 'categoria' => 'notificacoes', 'descricao' => 'Ativar notificações por SMS'],
            ['chave' => 'notificacoes_push', 'valor' => '1', 'tipo' => 'boolean', 'categoria' => 'notificacoes', 'descricao' => 'Ativar notificações push'],
            
            // Configurações de relatórios
            ['chave' => 'formato_padrao_relatorio', 'valor' => 'pdf', 'tipo' => 'string', 'categoria' => 'relatorios', 'descricao' => 'Formato padrão dos relatórios'],
            
            // Configurações de backup
            ['chave' => 'backup_automatico', 'valor' => '1', 'tipo' => 'boolean', 'categoria' => 'backup', 'descricao' => 'Ativar backup automático'],
            ['chave' => 'frequencia_backup', 'valor' => 'diario', 'tipo' => 'string', 'categoria' => 'backup', 'descricao' => 'Frequência do backup automático'],
            ['chave' => 'manter_backups_por', 'valor' => '30', 'tipo' => 'integer', 'categoria' => 'backup', 'descricao' => 'Manter backups por quantos dias'],
            
            // Configurações de performance
            ['chave' => 'cache_ativo', 'valor' => '1', 'tipo' => 'boolean', 'categoria' => 'performance', 'descricao' => 'Ativar cache do sistema'],
            ['chave' => 'tempo_cache_minutos', 'valor' => '60', 'tipo' => 'integer', 'categoria' => 'performance', 'descricao' => 'Tempo de cache em minutos'],
            ['chave' => 'compressao_ativa', 'valor' => '1', 'tipo' => 'boolean', 'categoria' => 'performance', 'descricao' => 'Ativar compressão de resposta'],
            
            // Configurações de sistema
            ['chave' => 'modo_manutencao', 'valor' => '0', 'tipo' => 'boolean', 'categoria' => 'sistema', 'descricao' => 'Ativar modo de manutenção'],
            ['chave' => 'mensagem_manutencao', 'valor' => 'Sistema em manutenção. Tente novamente em alguns minutos.', 'tipo' => 'string', 'categoria' => 'sistema', 'descricao' => 'Mensagem exibida no modo manutenção']
        ];
        
        foreach ($configuracoes as $config) {
            self::firstOrCreate(
                ['chave' => $config['chave']],
                $config
            );
        }
    }
}