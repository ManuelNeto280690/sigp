<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RemoverPorto extends Command
{
    protected $signature = 'porto:remover {slug} {dominio} {path?} {db_nome?} {db_usuario?}';
    protected $description = 'Remove subdomínio, pasta em public_html, base de dados e usuário MySQL via WHM/cPanel';

    public function handle(): int
    {
        $slug = (string) $this->argument('slug');
        $dominio = (string) $this->argument('dominio');
        $path = (string) ($this->argument('path') ?? '');
        $dbFull = (string) ($this->argument('db_nome') ?? '');
        $userFull = (string) ($this->argument('db_usuario') ?? '');

        $this->info("Remoção solicitada para {$slug} ({$dominio})");

        // Preferir DOMINIO_BASE; fallback para splitDomain
        $baseRoot = (string) (config('services.whm.domain_base') ?? env('DOMINIO_BASE', ''));
        if ($baseRoot !== '') {
            $sub = $slug;
            $root = $baseRoot;
        } else {
            [$sub, $root] = $this->splitDomain($dominio);
        }

        // 1) Remover subdomínio com duas tentativas (rótulo e FQDN)
        $okSub = $this->removerSubdominioWhm($sub, $root);
        if (!$okSub) {
            $this->warn("Tentativa alternativa: remover FQDN {$sub}.{$root}");
            $okSub = $this->removerSubdominioWhm("{$sub}.{$root}", $root);
        }

        // 2) Remover pasta (Fileman), se path estiver disponível
        $okDir = true;
        if ($path !== '') {
            $okDir = $this->removerDiretorioWhm($path);
        } else {
            $this->warn('Path não informado; pulando remoção de diretório.');
        }

        // 3) Remover DB e usuário (Mysql), se informados
        $okDb = true;
        if ($dbFull !== '') {
            $okDb = $this->removerDatabaseWhm($dbFull, $userFull);
        } else {
            $this->warn('DB/Usuário não informados; pulando remoção de MySQL.');
        }

        Log::info('[RemoverPorto] resumo', ['slug' => $slug, 'sub' => $sub, 'root' => $root, 'okSub' => $okSub, 'okDir' => $okDir, 'okDb' => $okDb]);

        if ($okSub && $okDir && $okDb) {
            $this->info('Recursos removidos com sucesso.');
            return 0;
        }

        $this->error('Nem todos os recursos foram removidos com sucesso. Verifique os logs.');
        return 1;
    }

    private function removerSubdominioWhm(string $subdominio, string $dominioPrincipal): bool
    {
        $host = (string) env('WHM_HOST');
        $whmUser = (string) env('WHM_USER');
        $token = (string) env('WHM_TOKEN');
        $cpanelUser = (string) env('CPANEL_USER');

        if (!$host || !$whmUser || !$token || !$cpanelUser) {
            $this->error('Config WHM/CPANEL incompleta para remover subdomínio.');
            return false;
        }

        $host = $this->sanitizeWhmHost($host);
        $protocol = env('WHM_PROTOCOL', 'https');
        $port = (int) env('WHM_PORT', 2087);
        $verify = filter_var(env('WHM_SSL_VERIFY', true), FILTER_VALIDATE_BOOL);

        $client = Http::withOptions(['verify' => $verify])
            ->withHeaders(['Authorization' => "whm {$whmUser}:{$token}"]);

        // API2
        $baseUrlApi2 = "{$protocol}://{$host}:{$port}/json-api/cpanel";
        $params = [
            'cpanel_jsonapi_user' => $cpanelUser,
            'cpanel_jsonapi_apiversion' => '2',
            'cpanel_jsonapi_module' => 'SubDomain',
            'cpanel_jsonapi_func' => 'delsubdomain',
            'domain' => $subdominio,
            'rootdomain' => $dominioPrincipal,
        ];
        $urlApi2 = $baseUrlApi2 . '?' . http_build_query($params);
        $this->line("WHM DelSub (API2) URL: {$urlApi2}");

        try {
            $resp = $client->get($urlApi2);
            Log::info('[RemoverPorto] delsubdomain API2', ['status' => $resp->status(), 'body' => mb_substr($resp->body(), 0, 500), 'domain' => $subdominio, 'root' => $dominioPrincipal]);
            if ($resp->successful() && $this->uapiOk($resp)) {
                return true;
            }
        } catch (\Throwable $e) {
            Log::error('[RemoverPorto] delsubdomain API2 exceção', ['msg' => $e->getMessage()]);
        }

        // Fallback UAPI execute
        $baseUrlUapi = "{$protocol}://{$host}:{$port}/execute/SubDomain/delsubdomain";
        $urlUapi = $baseUrlUapi . '?domain=' . urlencode($subdominio) . '&rootdomain=' . urlencode($dominioPrincipal);
        $this->line("WHM DelSub (UAPI) URL: {$urlUapi}");

        try {
            $resp2 = $client->get($urlUapi);
            Log::info('[RemoverPorto] delsubdomain UAPI', ['status' => $resp2->status(), 'body' => mb_substr($resp2->body(), 0, 500), 'domain' => $subdominio, 'root' => $dominioPrincipal]);
            if ($resp2->successful() && $this->uapiOk($resp2)) {
                return true;
            }
        } catch (\Throwable $e) {
            Log::error('[RemoverPorto] delsubdomain UAPI exceção', ['msg' => $e->getMessage()]);
        }

        $this->error("Falha ao remover subdomínio {$subdominio} ({$dominioPrincipal}).");
        return false;
    }

    private function removerDiretorioWhm(string $absolutePath): bool
    {
        $whmHost = (string) env('WHM_HOST');
        $whmUser = (string) env('WHM_USER');
        $whmToken = (string) env('WHM_TOKEN');
        $cpanelUser = (string) env('CPANEL_USER');
        if (!$whmHost || !$whmUser || !$whmToken || !$cpanelUser) {
            $this->error('Config WHM/CPANEL incompleta para remover diretório.');
            return false;
        }

        $host = $this->sanitizeWhmHost($whmHost);
        $protocol = env('WHM_PROTOCOL', 'https');
        $port = (int) env('WHM_PORT', 2087);
        $verify = filter_var(env('WHM_SSL_VERIFY', true), FILTER_VALIDATE_BOOL);

        $client = Http::withOptions(['verify' => $verify])
            ->withHeaders(['Authorization' => "whm {$whmUser}:{$whmToken}"]);

        // Separar dir e file
        $relative = ltrim(str_replace("/home/{$cpanelUser}/", '', rtrim($absolutePath, "/\\")), "/\\"); // ex.: public_html/porto-de-lobito

        // Troca para Fileman::fileop (API2), usando op=trash e sourcefiles relativo ao /home
        $baseUrlApi2 = "{$protocol}://{$host}:{$port}/json-api/cpanel";
        $params = [
            'cpanel_jsonapi_user' => $cpanelUser,
            'cpanel_jsonapi_apiversion' => '2',
            'cpanel_jsonapi_module' => 'Fileman',
            'cpanel_jsonapi_func' => 'fileop',
            'op' => 'trash',
            'sourcefiles' => $relative, // relativo ao /home/{user}
            'doubledecode' => 1,
        ];
        $urlApi2 = $baseUrlApi2 . '?' . http_build_query($params);
        $this->line("WHM Fileman fileop (API2) URL: {$urlApi2}");

        try {
            $resp = $client->get($urlApi2);
            Log::info('[RemoverPorto] fileman fileop API2', [
                'status' => $resp->status(),
                'body' => mb_substr($resp->body(), 0, 500),
                'sourcefiles' => $params['sourcefiles'],
                'op' => 'trash',
            ]);
            if ($resp->successful() && $this->uapiOk($resp)) {
                return true;
            }
        } catch (\Throwable $e) {
            Log::error('[RemoverPorto] fileman API2 exceção', ['msg' => $e->getMessage()]);
        }

        $this->error("Falha ao remover diretório {$absolutePath}.");
        return false;
    }

    private function removerDatabaseWhm(string $dbFull, ?string $userFull): bool
    {
        $whmHost = (string) env('WHM_HOST');
        $whmUser = (string) env('WHM_USER');
        $whmToken = (string) env('WHM_TOKEN');
        $cpanelUser = (string) env('CPANEL_USER');
        if (!$whmHost || !$whmUser || !$whmToken || !$cpanelUser) {
            $this->error('Config WHM/CPANEL incompleta para remover banco.');
            return false;
        }

        $host = $this->sanitizeWhmHost($whmHost);
        $protocol = env('WHM_PROTOCOL', 'https');
        $port = (int) env('WHM_PORT', 2087);
        $verify = filter_var(env('WHM_SSL_VERIFY', true), FILTER_VALIDATE_BOOL);

        $client = Http::withOptions(['verify' => $verify])
            ->withHeaders(['Authorization' => "whm {$whmUser}:{$whmToken}"]);

        $okDb = false;
        $okUser = !empty($userFull) ? false : true;

        // UAPI v3 via json-api/cpanel: Mysql::delete_database
        $baseUrlV3 = "{$protocol}://{$host}:{$port}/json-api/cpanel";
        $paramsDb = [
            'cpanel_jsonapi_user' => $cpanelUser,
            'cpanel_jsonapi_apiversion' => '3',
            'cpanel_jsonapi_module' => 'Mysql',
            'cpanel_jsonapi_func' => 'delete_database',
            'name' => $dbFull,
        ];
        $urlDb = $baseUrlV3 . '?' . http_build_query($paramsDb);
        $this->line("WHM Mysql delete_database (UAPI v3): {$urlDb}");
        try {
            $respDb = $client->get($urlDb);
            Log::info('[RemoverPorto] delete_database UAPI v3', ['status' => $respDb->status(), 'body' => mb_substr($respDb->body(), 0, 500)]);
            $okDb = $respDb->successful() && $this->uapiOk($respDb);
            if (!$okDb) {
                $this->warn('Não foi possível remover o banco (pode já não existir).');
            }
        } catch (\Throwable $e) {
            Log::error('[RemoverPorto] delete_database exceção', ['msg' => $e->getMessage()]);
        }

        // UAPI v3 via json-api/cpanel: Mysql::delete_user
        if ($userFull) {
            $paramsUser = [
                'cpanel_jsonapi_user' => $cpanelUser,
                'cpanel_jsonapi_apiversion' => '3',
                'cpanel_jsonapi_module' => 'Mysql',
                'cpanel_jsonapi_func' => 'delete_user',
                'name' => $userFull,
            ];
            $urlUser = $baseUrlV3 . '?' . http_build_query($paramsUser);
            $this->line("WHM Mysql delete_user (UAPI v3): {$urlUser}");
            try {
                $respUser = $client->get($urlUser);
                Log::info('[RemoverPorto] delete_user UAPI v3', ['status' => $respUser->status(), 'body' => mb_substr($respUser->body(), 0, 500)]);
                $okUser = $respUser->successful() && $this->uapiOk($respUser);
                if (!$okUser) {
                    $this->warn('Não foi possível remover o usuário (pode já não existir).');
                }
            } catch (\Throwable $e) {
                Log::error('[RemoverPorto] delete_user exceção', ['msg' => $e->getMessage()]);
            }
        }

        return ($okDb && $okUser);
    }

    private function splitDomain(string $full): array
    {
        $full = trim($full);
        $parts = explode('.', $full);
        if (count($parts) < 2) {
            return [$full, $full];
        }
        $root = implode('.', array_slice($parts, -2));
        $sub = implode('.', array_slice($parts, 0, -2));
        return [$sub, $root];
    }

    private function sanitizeWhmHost(string $host): string
    {
        $h = trim($host);
        $h = preg_replace('#^(https?|ftp)://#', '', $h);
        if (str_contains($h, '/')) {
            $h = explode('/', $h)[0];
        }
        if (str_contains($h, ':')) {
            $h = explode(':', $h)[0];
        }
        return rtrim($h, '/');
    }

    private function uapiOk(\Illuminate\Http\Client\Response $resp): bool
    {
        if (!$resp->successful()) {
            return false;
        }
        $json = json_decode($resp->body(), true);
        if (!is_array($json)) {
            return false;
        }

        // Falhas explícitas
        if (!empty($json['error'])) {
            return false;
        }
        if (isset($json['cpanelresult']['error']) && !empty($json['cpanelresult']['error'])) {
            return false;
        }

        // UAPI v3 padrão
        if (isset($json['status'])) {
            return (int) $json['status'] === 1;
        }
        if (isset($json['result']['status'])) {
            return (int) $json['result']['status'] === 1;
        }

        // API2-like: qualquer data.result==1
        if (isset($json['cpanelresult']['data'])) {
            $data = $json['cpanelresult']['data'];
            if (is_array($data)) {
                // data pode ser array de objetos
                foreach ($data as $item) {
                    if (is_array($item) && isset($item['result'])) {
                        if ((int) $item['result'] === 1) {
                            return true;
                        }
                    }
                }
                // Se nenhum item result==1, considerar falha
                return false;
            }
            // data como objeto simples
            if (is_array($data) && isset($data['result'])) {
                return (int) $data['result'] === 1;
            }
        }

        return false;
    }
}