<?php

namespace Database\Seeders;

use App\Models\Configuracao;
use Illuminate\Database\Seeder;

class ConfiguracaoSeeder extends Seeder
{
    public function run(): void
    {
        $configuracoes = [
            // === CONFIGURAÇÕES GERAIS ===
            [
                'chave' => 'nome_sistema',
                'valor' => 'SIGP - Sistema Integrado de Gestão Portuária',
                'tipo' => 'string',
                'categoria' => 'geral',
                'descricao' => 'Nome oficial do sistema',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'versao_sistema',
                'valor' => '1.0.0',
                'tipo' => 'string',
                'categoria' => 'geral',
                'descricao' => 'Versão atual do sistema',
                'editavel' => false,
                'is_active' => true
            ],
            [
                'chave' => 'nome_empresa',
                'valor' => 'Porto de Soyo',
                'tipo' => 'string',
                'categoria' => 'empresa',
                'descricao' => 'Nome da empresa/organização',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'razao_social',
                'valor' => 'Administração Portuária de Soyo',
                'tipo' => 'string',
                'categoria' => 'empresa',
                'descricao' => 'Razão social da empresa',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'cnpj_empresa',
                'valor' => '',
                'tipo' => 'string',
                'categoria' => 'empresa',
                'descricao' => 'CNPJ/NIF da empresa',
                'editavel' => true,
                'is_active' => true
            ],

            // === LOGOTIPOS E IDENTIDADE VISUAL ===
            [
                'chave' => 'logo_sistema',
                'valor' => '',
                'tipo' => 'file',
                'categoria' => 'visual',
                'descricao' => 'Logotipo principal do sistema (formato: PNG, JPG - máx: 2MB)',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'logo_relatorios',
                'valor' => '',
                'tipo' => 'file',
                'categoria' => 'visual',
                'descricao' => 'Logotipo para relatórios e documentos (formato: PNG, JPG - máx: 2MB)',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'favicon',
                'valor' => '',
                'tipo' => 'file',
                'categoria' => 'visual',
                'descricao' => 'Ícone do navegador (formato: ICO, PNG - 32x32px)',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'cor_primaria',
                'valor' => '#0086e1',
                'tipo' => 'color',
                'categoria' => 'visual',
                'descricao' => 'Cor primária do sistema',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'cor_secundaria',
                'valor' => '#01043d',
                'tipo' => 'color',
                'categoria' => 'visual',
                'descricao' => 'Cor secundária do sistema',
                'editavel' => true,
                'is_active' => true
            ],

            // === CABEÇALHO DE DOCUMENTOS ===
            [
                'chave' => 'cabecalho_documentos',
                'valor' => json_encode([
                    'titulo' => 'PORTO DE SOYO',
                    'subtitulo' => 'Sistema Integrado de Gestão Portuária',
                    'endereco' => 'Soyo, Província do Zaire, Angola',
                    'telefone' => '+244 xxx xxx xxx',
                    'email' => 'contato@portodesoyo.ao',
                    'website' => 'www.portodesoyo.ao'
                ]),
                'tipo' => 'json',
                'categoria' => 'documentos',
                'descricao' => 'Informações do cabeçalho para documentos e relatórios',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'rodape_relatorios',
                'valor' => 'Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária',
                'tipo' => 'text',
                'categoria' => 'documentos',
                'descricao' => 'Texto do rodapé para relatórios',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'assinatura_digital',
                'valor' => '',
                'tipo' => 'file',
                'categoria' => 'documentos',
                'descricao' => 'Imagem da assinatura digital para documentos oficiais',
                'editavel' => true,
                'is_active' => true
            ],

            // === DADOS DE CONTATO ===
            [
                'chave' => 'endereco_completo',
                'valor' => 'Porto de Soyo, Província do Zaire, Angola',
                'tipo' => 'text',
                'categoria' => 'contato',
                'descricao' => 'Endereço completo da organização',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'local_empresa',
                'valor' => 'Soyo, Zaire, Angola',
                'tipo' => 'string',
                'categoria' => 'empresa',
                'descricao' => 'Localização padrão da empresa (cidade, província, país) para consultas de previsão do tempo',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'telefone_contato',
                'valor' => '+244 xxx xxx xxx',
                'tipo' => 'string',
                'categoria' => 'contato',
                'descricao' => 'Telefone principal de contato',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'email_contato',
                'valor' => 'contato@portodesoyo.ao',
                'tipo' => 'email',
                'categoria' => 'contato',
                'descricao' => 'Email principal de contato',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'website',
                'valor' => 'www.portodesoyo.ao',
                'tipo' => 'url',
                'categoria' => 'contato',
                'descricao' => 'Website oficial',
                'editavel' => true,
                'is_active' => true
            ],

            // === CONFIGURAÇÕES REGIONAIS ===
            [
                'chave' => 'fuso_horario',
                'valor' => 'Africa/Luanda',
                'tipo' => 'string',
                'categoria' => 'regional',
                'descricao' => 'Fuso horário padrão do sistema',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'idioma_padrao',
                'valor' => 'pt_BR',
                'tipo' => 'string',
                'categoria' => 'regional',
                'descricao' => 'Idioma padrão do sistema',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'moeda_padrao',
                'valor' => 'AOA',
                'tipo' => 'string',
                'categoria' => 'regional',
                'descricao' => 'Moeda padrão (Kwanza Angolano)',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'formato_data',
                'valor' => 'd/m/Y',
                'tipo' => 'string',
                'categoria' => 'regional',
                'descricao' => 'Formato de exibição de datas',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'formato_hora',
                'valor' => 'H:i:s',
                'tipo' => 'string',
                'categoria' => 'regional',
                'descricao' => 'Formato de exibição de horas',
                'editavel' => true,
                'is_active' => true
            ],

            // === CONFIGURAÇÕES DE SEGURANÇA ===
            [
                'chave' => 'tentativas_maximas_login',
                'valor' => '5',
                'tipo' => 'integer',
                'categoria' => 'seguranca',
                'descricao' => 'Número máximo de tentativas de login',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'tempo_sessao',
                'valor' => '120',
                'tipo' => 'integer',
                'categoria' => 'seguranca',
                'descricao' => 'Tempo de sessão em minutos',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'exigir_2fa',
                'valor' => '0',
                'tipo' => 'boolean',
                'categoria' => 'seguranca',
                'descricao' => 'Exigir autenticação de dois fatores',
                'editavel' => true,
                'is_active' => true
            ],

            // === CONFIGURAÇÕES DE EMAIL ===
            [
                'chave' => 'email_remetente',
                'valor' => 'noreply@portodesoyo.ao',
                'tipo' => 'email',
                'categoria' => 'email',
                'descricao' => 'Email remetente padrão do sistema',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'nome_remetente',
                'valor' => 'SIGP - Porto de Soyo',
                'tipo' => 'string',
                'categoria' => 'email',
                'descricao' => 'Nome do remetente nos emails',
                'editavel' => true,
                'is_active' => true
            ],

            // === CONFIGURAÇÕES DE NOTIFICAÇÕES ===
            [
                'chave' => 'notificacoes_email',
                'valor' => '1',
                'tipo' => 'boolean',
                'categoria' => 'notificacoes',
                'descricao' => 'Ativar notificações por email',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'notificacoes_sms',
                'valor' => '0',
                'tipo' => 'boolean',
                'categoria' => 'notificacoes',
                'descricao' => 'Ativar notificações por SMS',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'notificacoes_push',
                'valor' => '1',
                'tipo' => 'boolean',
                'categoria' => 'notificacoes',
                'descricao' => 'Ativar notificações push',
                'editavel' => true,
                'is_active' => true
            ],

            // === CONFIGURAÇÕES DE BACKUP ===
            [
                'chave' => 'backup_automatico',
                'valor' => '1',
                'tipo' => 'boolean',
                'categoria' => 'backup',
                'descricao' => 'Ativar backup automático',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'frequencia_backup',
                'valor' => 'diario',
                'tipo' => 'string',
                'categoria' => 'backup',
                'descricao' => 'Frequência do backup (diario, semanal, mensal)',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'retencao_backup',
                'valor' => '30',
                'tipo' => 'integer',
                'categoria' => 'backup',
                'descricao' => 'Dias de retenção dos backups',
                'editavel' => true,
                'is_active' => true
            ],

            // === CONFIGURAÇÕES DE RELATÓRIOS ===
            [
                'chave' => 'formato_relatorio_padrao',
                'valor' => 'pdf',
                'tipo' => 'string',
                'categoria' => 'relatorios',
                'descricao' => 'Formato padrão para relatórios (pdf, excel, csv)',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'incluir_logo_relatorios',
                'valor' => '1',
                'tipo' => 'boolean',
                'categoria' => 'relatorios',
                'descricao' => 'Incluir logotipo nos relatórios',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'marca_dagua_relatorios',
                'valor' => 'CONFIDENCIAL',
                'tipo' => 'string',
                'categoria' => 'relatorios',
                'descricao' => 'Marca d\'água nos relatórios',
                'editavel' => true,
                'is_active' => true
            ],

            // === CONFIGURAÇÕES OPERACIONAIS ===
            [
                'chave' => 'horario_funcionamento',
                'valor' => json_encode([
                    'segunda' => ['inicio' => '06:00', 'fim' => '18:00'],
                    'terca' => ['inicio' => '06:00', 'fim' => '18:00'],
                    'quarta' => ['inicio' => '06:00', 'fim' => '18:00'],
                    'quinta' => ['inicio' => '06:00', 'fim' => '18:00'],
                    'sexta' => ['inicio' => '06:00', 'fim' => '18:00'],
                    'sabado' => ['inicio' => '06:00', 'fim' => '12:00'],
                    'domingo' => ['inicio' => null, 'fim' => null]
                ]),
                'tipo' => 'json',
                'categoria' => 'operacional',
                'descricao' => 'Horário de funcionamento do porto',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'capacidade_maxima_embarcacoes',
                'valor' => '50',
                'tipo' => 'integer',
                'categoria' => 'operacional',
                'descricao' => 'Capacidade máxima de embarcações simultâneas',
                'editavel' => true,
                'is_active' => true
            ],
            [
                'chave' => 'tempo_maximo_atracacao',
                'valor' => '72',
                'tipo' => 'integer',
                'categoria' => 'operacional',
                'descricao' => 'Tempo máximo de atracação em horas',
                'editavel' => true,
                'is_active' => true
            ]
        ];

        foreach ($configuracoes as $config) {
            Configuracao::updateOrCreate(
                ['chave' => $config['chave']],
                $config
            );
        }
    }
}