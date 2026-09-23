<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Models\Porto;
use Illuminate\Support\Facades\Log;

class CriarPorto extends Command
{
    protected $signature = 'porto:criar {nome}';
    protected $description = 'Cria uma nova instância SaaS completa para um porto';

    public function handle()
    {
        $nome = $this->argument('nome');
        $slug = Str::slug($nome);

        // Domínio base do painel SaaS (exemplo: teudominio.com)
        $dominioBase = parse_url(config('app.url'), PHP_URL_HOST);
        $dominio = "{$slug}.{$dominioBase}";

        Log::info('[CriarPorto] Início', [
            'nome' => $nome,
            'slug' => $slug,
            'dominio' => $dominio,
            'os_family' => PHP_OS_FAMILY ?? null,
        ]);
        $this->info("Iniciando criação do porto: {$nome} ({$slug})");
        $this->info("Subdomínio: {$dominio}");

        // Pastas base (com fallback amigável ao Windows)
        $portosDir = env('PORTOS_DIR', storage_path('app/portos'));
        if (!File::exists($portosDir)) {
            File::makeDirectory($portosDir, 0755, true);
        }
        // Normaliza separadores e remove barra invertida
        $portoPath = rtrim($portosDir, "/\\") . '/' . $slug;

        $skipClone = false;
        if (File::exists($portoPath)) {
            // Pasta existente: validar se é uma app Laravel (tem artisan)
            if (!File::exists($portoPath . '/artisan')) {
                $this->warn("Pasta existe sem app válida; limpando para recriar: {$portoPath}");
                File::deleteDirectory($portoPath);
            } else {
                $this->warn("Pasta já existe e parece válida; pulando clone: {$portoPath}");
                $skipClone = true;
            }
        }

        // 1) Clonar repositório apenas se necessário
        if (!$skipClone) {
            $repoGit = env('GIT_REPO_BASE_APP', 'https://github.com/ManuelNeto280690/14440acconting.git');
            $branches = array_filter([
                env('GIT_BRANCH', 'main'), // default para main
                'main',
                'master',
            ]);
        
            $cloned = false;
            foreach ($branches as $branch) {
                $this->info("Clonando repositório ({$repoGit}) branch {$branch} em {$portoPath} ...");
                $cloneCmd = "git clone --branch {$branch} {$repoGit} " . escapeshellarg($portoPath);
                exec($cloneCmd, $out, $code);
                if ($code === 0) {
                    $cloned = true;
                    break;
                }
            }
            if (!$cloned) {
                $this->error("Erro ao clonar o repositório; verifique GIT_BRANCH e permissões.");
                return 1;
            }
            File::deleteDirectory($portoPath . '/.git');
        }

        // Definir credenciais de DB antes do .env
        $dbNome = "sigpangola_{$slug}";
        $dbUser = "sigpangola_{$slug}";
        $dbPass = Str::random(16);

        // 2) Criar database via WHM (condicional e modo estrito)
        $whmConfigured = env('WHM_HOST') && env('WHM_USER') && env('WHM_TOKEN');
        $whmStrict = filter_var(env('WHM_STRICT', true), FILTER_VALIDATE_BOOL);
        $cpanelUser = env('CPANEL_USER');
        $dominioBase = parse_url(config('app.url'), PHP_URL_HOST);
        $isLinux = (PHP_OS_FAMILY ?? '') === 'Linux';

        // Definir o caminho do app: se WHM/CPANEL, usar docroot pai do subdomínio
        // Definir a pasta do subdomínio
        if ($whmConfigured && $cpanelUser) {
            $portoPath = "/home/{$cpanelUser}/public_html/{$slug}";
        } else {
            $portosDir = env('PORTOS_DIR', storage_path('app/portos'));
            if (!File::exists($portosDir)) {
                File::makeDirectory($portosDir, 0755, true);
            }
            $portoPath = rtrim($portosDir, "/\\") . '/' . $slug;
        }
        
        // Subdomínio (ordem correta; uma única chamada)
        if ($whmConfigured && $cpanelUser) {
            $this->info("Criando subdomínio {$slug}.{$dominioBase} via WHM...");
            $subOk = $this->criarSubdominioWhm($slug, $dominioBase, $portoPath);
            if (!$subOk) {
                if ($whmStrict) {
                    $this->error("Erro ao criar subdomínio no WHM");
                    return 1;
                }
                $this->warn("WHM falhou na criação do subdomínio; prosseguindo (dev).");
            }
        }

        // Credenciais DB/usuário com hífen
        $ids = $this->buildMysqlIdentifiers($slug);
        $dbNome = $ids['db'];
        $dbUser = $ids['user'];
        $dbPass = Str::random(16);
        
        // Criar DB/usuário/privilégios
        $this->info("Criando database {$dbNome} e usuário {$dbUser} via WHM...");
        if (!$this->criarDatabaseWhm($dbNome, $dbUser, $dbPass)) {
            $this->error("Erro ao criar database no WHM");
            return 1;
        }

        // CLONAR repositório NA PASTA DO SUBDOMÍNIO, APÓS DB OK
        $skipClone = File::exists($portoPath . '/artisan');
        if ($skipClone) {
            $this->warn("Pasta já existe e parece válida; pulando clone: {$portoPath}");
        } else {
            $this->info("Clonando repositório ({$repoGit}) em {$portoPath} ...");
            $cloned = false;
            foreach ($branches as $branch) {
                $this->info("Tentando branch {$branch}...");
                $cloneCmd = "git clone --branch {$branch} {$repoGit} " . escapeshellarg($portoPath);
                exec($cloneCmd, $out, $code);
                if ($code === 0) {
                    $cloned = true;
                    break;
                }
            }
            if (!$cloned) {
                $this->error("Erro ao clonar o repositório; verifique GIT_BRANCH e permissões.");
                return 1;
            }
            File::deleteDirectory($portoPath . '/.git');
        }

        // 5) Criar .env e configurar app (composer, key, migrate)
        $this->info("Criando arquivo .env...");
        $envContent = $this->montarEnv($nome, "{$slug}.{$dominioBase}", $dbNome, $dbUser, $dbPass, $slug);
        File::put($portoPath . '/.env', $envContent);

        $runUser = env('PORTO_RUN_USER', 'sigpangola');
        $isLinux = (PHP_OS_FAMILY ?? '') === 'Linux';
        if ($isLinux) {
            shell_exec("chown -R {$runUser}:{$runUser} " . escapeshellarg($portoPath));
        } else {
            $this->warn("chown não aplicável neste sistema.");
        }

        $this->info("Executando composer install...");
        $this->execInPath($portoPath, 'composer install --no-interaction --no-dev --prefer-dist');

        $this->info("Gerando app key...");
        $this->execInPath($portoPath, 'php artisan key:generate --force');

        $this->info("Executando migrations...");
        $this->execInPath($portoPath, 'php artisan migrate --force');

        // 6) Serviço systemd (apenas Linux) e persistência (idempotente)
        if ($isLinux) {
            $this->info("Criando serviço systemd para worker...");
            if (!$this->criarWorkerSystemd($slug, $portoPath)) {
                $this->error("Erro ao criar serviço systemd");
                return 1;
            }
            $workerStatus = trim($this->execAndReturn("systemctl is-active queue-{$slug}.service"));
        } else {
            $this->warn("Systemd indisponível no Windows; marcando worker como 'inactive'.");
            $workerStatus = 'inactive';
        }

        // 7) Registrar no banco SaaS (idempotente por slug)
        $existing = Porto::where('slug', $slug)->first();
        if ($existing) {
            $existing->fill([
                'nome' => $nome,
                'dominio' => $dominio,
                'db_nome' => $dbNome,
                'db_usuario' => $dbUser,
                'db_senha' => $dbPass,
                'path' => $portoPath,
                'worker_status' => $workerStatus,
            ])->save();
            Log::info('[CriarPorto] Porto atualizado', ['porto_id' => $existing->id, 'slug' => $slug]);
            $porto = $existing;
            $this->info("Porto já existia; dados atualizados.");
        } else {
            $porto = Porto::create([
                'nome' => $nome,
                'slug' => $slug,
                'dominio' => $dominio,
                'db_nome' => $dbNome,
                'db_usuario' => $dbUser,
                'db_senha' => $dbPass,
                'path' => $portoPath,
                'worker_status' => $workerStatus,
            ]);
            Log::info('[CriarPorto] Porto persistido', ['porto_id' => $porto->id, 'slug' => $slug]);
            $this->info("Porto criado com sucesso!");
        }

        $this->line("Nome: {$nome}");
        $this->line("Slug: {$slug}");
        $this->line("Domínio: {$dominio}");
        $this->line("Path: {$portoPath}");
        $this->line("DB: {$dbNome} / {$dbUser} / {$dbPass}");
        $this->line("Status worker: {$workerStatus}");

        return 0;
    }

    private function criarDatabaseWhm(string $dbFull, string $userFull, string $dbPassword): bool
    {
        $whmHost = $this->sanitizeWhmHost((string) (config('services.whm.host') ?? env('WHM_HOST')));
        $whmUser = (string) (config('services.whm.user') ?? env('WHM_USER', 'root'));
        $whmToken = (string) (config('services.whm.token') ?? env('WHM_TOKEN'));
        $cpanelUser = (string) (config('services.whm.cpanel_user') ?? env('CPANEL_USER'));
        $verifySsl = filter_var(env('WHM_SSL_VERIFY', true), FILTER_VALIDATE_BOOL);

        if (!$whmHost || !$whmToken || !$cpanelUser) {
            $this->error('Config WHM/CPANEL incompleta para operar banco.');
            return false;
        }

        $authHeader = ['Authorization' => "whm {$whmUser}:{$whmToken}"];
        $base = "https://{$whmHost}:2087/json-api/cpanel";
        $common = [
            'cpanel_jsonapi_user' => $cpanelUser,
            'cpanel_jsonapi_apiversion' => 3,
            'cpanel_jsonapi_output' => 'json',
            'cpanel_jsonapi_module' => 'Mysql',
        ];

        // 1) Create database (UAPI: Mysql::create_database) — usar nome completo com prefixo
        $createDbParams = array_merge($common, [
            'cpanel_jsonapi_func' => 'create_database',
            'name' => $dbFull,
        ]);
        $createDbUrl = $base . '?' . http_build_query($createDbParams);
        $respDb = \Http::withHeaders($authHeader)->withOptions(['verify' => $verifySsl])->get($createDbUrl);
        $this->line("WHM DB URL: {$createDbUrl}");
        $this->line("WHM DB Status: {$respDb->status()}");
        $this->line("WHM DB Body: " . $respDb->body());
        if (!$this->uapiOk($respDb)) {
            $this->error('Erro WHM ao criar DB');
            return false;
        }

        // 2) Create user (UAPI: Mysql::create_user) — usar nome completo com prefixo
        $createUserParams = array_merge($common, [
            'cpanel_jsonapi_func' => 'create_user',
            'name' => $userFull,
            'password' => $dbPassword,
        ]);
        $createUserUrl = $base . '?' . http_build_query($createUserParams);
        $respUser = \Http::withHeaders($authHeader)->withOptions(['verify' => $verifySsl])->get($createUserUrl);
        $this->line("WHM User URL: {$createUserUrl}");
        $this->line("WHM User Status: {$respUser->status()}");
        $this->line("WHM User Body: " . $respUser->body());
        if (!$this->uapiOk($respUser)) {
            $this->error('Erro WHM ao criar usuário');
            return false;
        }

        // 3) Set privileges (UAPI: Mysql::set_privileges_on_database)
        $setPrivParams = array_merge($common, [
            'cpanel_jsonapi_func' => 'set_privileges_on_database',
            'user' => $userFull,
            'database' => $dbFull,
            'privileges' => 'ALL PRIVILEGES',
        ]);
        $setPrivUrl = $base . '?' . http_build_query($setPrivParams);
        $respPriv = \Http::withHeaders($authHeader)->withOptions(['verify' => $verifySsl])->get($setPrivUrl);
        $this->line("WHM Priv URL: {$setPrivUrl}");
        $this->line("WHM Priv Status: {$respPriv->status()}");
        $this->line("WHM Priv Body: " . $respPriv->body());
        if (!$this->uapiOk($respPriv)) {
            $this->error('Erro WHM ao setar privilégios');
            return false;
        }

        $this->info('Banco, usuário e privilégios criados com sucesso via UAPI');
        return true;
    }

    private function criarSubdominioWhm(string $subdominio, string $dominioPrincipal, string $path): bool
    {
        $host = env('WHM_HOST');
        $whmUser = env('WHM_USER');
        $token = env('WHM_TOKEN');
        $cpanelUser = env('CPANEL_USER');

        if (empty($token) || empty($host) || empty($whmUser) || empty($cpanelUser)) {
            Log::error('[CriarPorto] Subdomínio env incompleto', [
                'WHM_HOST' => (bool) $host,
                'WHM_USER' => (bool) $whmUser,
                'WHM_TOKEN' => (bool) $token,
                'CPANEL_USER' => (bool) $cpanelUser,
            ]);
            $this->error('WHM_HOST, WHM_USER, WHM_TOKEN ou CPANEL_USER não configurados no .env');
            return false;
        }

        // Sanitiza host (remove http://, https://, portas e sufixos)
        $host = $this->sanitizeWhmHost($host);

        $protocol = env('WHM_PROTOCOL', 'https');
        $port = (int) env('WHM_PORT', 2087);
        $verify = filter_var(env('WHM_SSL_VERIFY', true), FILTER_VALIDATE_BOOL);
        $baseUrl = "{$protocol}://{$host}:{$port}/json-api/cpanel";

        // Deriva dir relativo ao home do cPanel a partir do $path informado
        // Ex.: /home/sigpangola/public_html/porto-de-lobito -> public_html/porto-de-lobito
        $dirRelative = ltrim(str_replace("/home/{$cpanelUser}/", '', rtrim($path, "/\\")), "/\\");
        $dirRelative = rtrim($dirRelative, "/\\"); // sem '/public'

        Log::info('[CriarPorto] Criar subdomínio', [
            'subdominio' => $subdominio,
            'rootdomain' => $dominioPrincipal,
            'dir_relative' => $dirRelative,
            'baseUrl' => $baseUrl,
            'verify' => $verify,
        ]);

        $client = Http::withOptions(['verify' => $verify])
            ->withHeaders(['Authorization' => "whm {$whmUser}:{$token}"]);

        // Pre-check de existência
        $listParams = [
            'cpanel_jsonapi_user' => $cpanelUser,
            'cpanel_jsonapi_apiversion' => '2',
            'cpanel_jsonapi_module' => 'SubDomain',
            'cpanel_jsonapi_func' => 'listsubdomains',
        ];
        try {
            $listRes = $client->get($baseUrl . '?' . http_build_query($listParams));
            $listJson = $listRes->json();
            $full = "{$subdominio}.{$dominioPrincipal}";
            $exists = false;

            if (isset($listJson['cpanelresult']['data']) && is_array($listJson['cpanelresult']['data'])) {
                foreach ($listJson['cpanelresult']['data'] as $entry) {
                    $name = $entry['domain'] ?? ($entry['subdomain'] ?? ($entry['name'] ?? null));
                    if ($name === $full || $name === $subdominio) {
                        $exists = true;
                        break;
                    }
                }
            }

            Log::info('[CriarPorto] Subdomínio pre-check', [
                'status' => $listRes->status(),
                'exists' => $exists,
                'preview' => mb_substr(json_encode($listJson), 0, 600),
            ]);

            if ($exists) {
                $this->warn("Subdomínio já existe: {$full}");
                return true;
            }

            // Criação via GET (SubDomain::addsubdomain)
            $addParams = [
                'cpanel_jsonapi_user' => $cpanelUser,
                'cpanel_jsonapi_apiversion' => '2',
                'cpanel_jsonapi_module' => 'SubDomain',
                'cpanel_jsonapi_func' => 'addsubdomain',
                'domain' => $subdominio,
                'rootdomain' => $dominioPrincipal,
                'dir' => $dirRelative,
            ];
            $addUrl = $baseUrl . '?' . http_build_query($addParams);
            $response = $client->get($addUrl);

            // Inicializações ANTES dos logs e validações — corrige Undefined $hasError
            $body = $response->body();
            $json = $response->json() ?? [];
            $cp = $json['cpanelresult'] ?? [];
            $hasError = isset($cp['error']) && !empty($cp['error']);
            $eventRes = isset($cp['event']['result']) ? (int) $cp['event']['result'] : null;
            if ($eventRes === 0) {
                $hasError = true;
            }

            // Logs de diagnóstico
            $this->line("WHM Sub URL: {$addUrl}");
            $this->line("WHM Sub Status: {$response->status()}");
            $this->line("WHM Sub Body: " . mb_substr($body, 0, 600));

            Log::info('[CriarPorto] Subdomínio resposta', [
                'status' => $response->status(),
                'has_error' => $hasError,
                'body_preview' => mb_substr($body, 0, 600),
                'json_preview' => mb_substr(json_encode($json), 0, 600),
            ]);

            if ($hasError) {
                $reason = $cp['error'] ?? ($cp['data'][0]['reason'] ?? 'Erro desconhecido');
                $this->error("Erro ao criar subdomínio: " . $reason);
                return false;
            }

            if (!$response->successful()) {
                $this->error("Falha WHM (HTTP {$response->status()}): " . mb_substr($body, 0, 400));
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('[CriarPorto] Subdomínio exceção', ['msg' => $e->getMessage()]);
            $this->error("Erro na API WHM para subdomínio: " . $e->getMessage());
            return false;
        }
    }

    private function montarEnv($nome, $dominio, $dbNome, $dbUser, $dbPass, $slug)
    {
        $appUrl = "https://{$dominio}";
        $redisPrefix = "{$slug}_";

        return <<<ENV
APP_NAME="{$nome}"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL={$appUrl}

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE={$dbNome}
DB_USERNAME={$dbUser}
DB_PASSWORD={$dbPass}

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_PREFIX={$redisPrefix}
ENV;
    }

    private function execInPath($path, $command)
    {
        $fullCmd = "cd " . escapeshellarg($path) . " && {$command} 2>&1";
        $this->info("Executando: {$command}");
        Log::info('[CriarPorto] execInPath', ['path' => $path, 'cmd' => $command]);
        $out = shell_exec($fullCmd);
        Log::info('[CriarPorto] execInPath retorno', [
            'cmd' => $command,
            'out_preview' => mb_substr($out ?? '', 0, 500),
            'len' => strlen($out ?? ''),
        ]);
        return $out;
    }

    private function criarWorkerSystemd($slug, $path): bool
    {
        $serviceName = "queue-{$slug}.service";
        $user = env('PORTO_RUN_USER', 'sigpangola');
        Log::info('[CriarPorto] Systemd gerar', [
            'service' => $serviceName,
            'user' => $user,
            'path' => $path,
        ]);

        $serviceContent = <<<EOD
        [Unit]
        Description=Laravel Queue Worker - {$slug}
        After=network.target
        
        [Service]
        User={$user}
        Group={$user}
        Restart=always
        RestartSec=5
        ExecStart=/usr/bin/php {$path}/artisan queue:work redis --sleep=3 --tries=3 --timeout=90
        StandardOutput=syslog
        StandardError=syslog
        SyslogIdentifier={$slug}-worker
        
        [Install]
        WantedBy=multi-user.target
        EOD;
    
        $serviceFile = "/etc/systemd/system/{$serviceName}";
    
        try {
            file_put_contents($serviceFile, $serviceContent);
            Log::info('[CriarPorto] systemctl daemon-reload');
            shell_exec('sudo systemctl daemon-reload');
            Log::info('[CriarPorto] systemctl enable', ['service' => $serviceName]);
            shell_exec("sudo systemctl enable {$serviceName}");
            Log::info('[CriarPorto] systemctl start', ['service' => $serviceName]);
            shell_exec("sudo systemctl start {$serviceName}");
    
            $status = trim(shell_exec("sudo systemctl is-active {$serviceName}"));
            Log::info('[CriarPorto] Systemd status', ['service' => $serviceName, 'status' => $status]);
            return $status === 'active';
    
        } catch (\Exception $e) {
            Log::error('[CriarPorto] Systemd erro', ['msg' => $e->getMessage()]);
            $this->error("Erro ao criar serviço systemd: " . $e->getMessage());
            return false;
        }
    }

    private function execAndReturn($cmd)
    {
        Log::info('[CriarPorto] exec', ['cmd' => $cmd]);
        $out = shell_exec($cmd . ' 2>&1');
        Log::info('[CriarPorto] exec retorno', [
            'cmd' => $cmd,
            'out_preview' => mb_substr($out ?? '', 0, 200),
            'len' => strlen($out ?? ''),
        ]);
        return $out;
    }

    // Helper para sanitizar WHM_HOST
    private function sanitizeWhmHost(string $host): string
    {
        $h = trim($host);
        // remove esquemas
        $h = preg_replace('#^(https?|ftp)://#', '', $h);
        // corta em primeira barra
        if (str_contains($h, '/')) {
            $h = explode('/', $h)[0];
        }
        // remove porta (evita conflitar com WHM_PORT)
        if (str_contains($h, ':')) {
            // cuidado com IPv6; ambiente atual usa IPv4/FQDN
            $h = explode(':', $h)[0];
        }
        return rtrim($h, '/');
    }

    // Mover este método para dentro da classe, antes da última chave de fechamento
    private function buildMysqlIdentifiers(string $slug): array
    {
        $cpanelUser = (string) (config('services.whm.cpanel_user') ?? env('CPANEL_USER', 'sigpangola'));

        // Slug com hífen: "Porto de Lobito" -> "porto-de-lobito"
        $base = Str::slug($slug, '-');
        if ($base === '') {
            $base = 'app';
        }

        $prefix = "{$cpanelUser}_";

        // Banco e usuário com hífen e prefixo completo (sem truncar)
        $db = $prefix . $base;
        $user = $prefix . $base;

        Log::info('[CriarPorto] MySQL identifiers', [
            'cpanel_user' => $cpanelUser,
            'db' => $db,
            'user' => $user,
        ]);

        return ['db' => $db, 'user' => $user];
    }

    private function uapiOk(\Illuminate\Http\Client\Response $resp): bool
    {
        if (!$resp->successful()) {
            return false;
        }
        $json = json_decode($resp->body(), true);
        if (is_array($json)) {
            // UAPI costuma retornar 'status' na raiz
            if (isset($json['status'])) {
                return (int) $json['status'] === 1;
            }
            // Alguns formatos trazem 'result.status'
            if (isset($json['result']['status'])) {
                return (int) $json['result']['status'] === 1;
            }
            // Compat: API2-like
            if (isset($json['cpanelresult']['data']['result'])) {
                return (int) $json['cpanelresult']['data']['result'] === 1;
            }
        }
        // Sem campo de status: considerar 200 como OK
        return true;
    }
}
