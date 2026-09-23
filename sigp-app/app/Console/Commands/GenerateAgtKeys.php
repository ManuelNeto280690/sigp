<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\AgtSignatureHelper;
use Illuminate\Support\Facades\Storage;

class GenerateAgtKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agt:generate-keys {--force : Overwrite keys if they exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the RSA 2048 key pair required for AGT signature.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (Storage::exists('agt/private_key.pem') && !$this->option('force')) {
            $this->warn('As chaves RSA já existem. Use --force para sobrescrever (Aviso: sobrescrever inutilizará a sequência de hashes atual).');
            return Command::FAILURE;
        }

        $this->info('Gerando chaves RSA 2048 para a AGT...');

        try {
            $keys = AgtSignatureHelper::generateKeys();
            
            $this->info('Chaves geradas e guardadas com sucesso no diretório storage/app/agt/');
            $this->line("A Chave Pública (para entregar à AGT) é:\n" . $keys['public']);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Falha ao gerar chaves: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
