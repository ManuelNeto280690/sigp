<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\ConfigHelper;

class ConfigCommand extends Command
{
    protected $signature = 'config:manage {action} {key?} {value?}';
    protected $description = 'Gerenciar configurações do sistema';

    public function handle()
    {
        $action = $this->argument('action');
        $key = $this->argument('key');
        $value = $this->argument('value');

        switch ($action) {
            case 'get':
                $this->getConfig($key);
                break;
            case 'set':
                $this->setConfig($key, $value);
                break;
            case 'list':
                $this->listConfigs();
                break;
            case 'clear-cache':
                $this->clearCache();
                break;
            default:
                $this->error('Ação inválida. Use: get, set, list, clear-cache');
        }
    }

    private function getConfig($key)
    {
        if (!$key) {
            $this->error('Chave é obrigatória para obter configuração');
            return;
        }

        $value = ConfigHelper::get($key);
        $this->info("Configuração '{$key}': " . ($value ?? 'null'));
    }

    private function setConfig($key, $value)
    {
        if (!$key || !$value) {
            $this->error('Chave e valor são obrigatórios');
            return;
        }

        if (ConfigHelper::set($key, $value)) {
            $this->info("Configuração '{$key}' definida com sucesso");
        } else {
            $this->error("Erro ao definir configuração '{$key}'");
        }
    }

    private function listConfigs()
    {
        $configs = ConfigHelper::all();
        
        $this->table(['Chave', 'Valor'], 
            collect($configs)->map(function ($value, $key) {
                return [$key, is_array($value) ? json_encode($value) : $value];
            })->toArray()
        );
    }

    private function clearCache()
    {
        ConfigHelper::clearCache();
        $this->info('Cache de configurações limpo com sucesso');
    }
}