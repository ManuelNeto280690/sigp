<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class NovoPedidoNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;

    public function __construct(Pedido $pedido)
    {
        $this->pedido = $pedido;
    }

    public function build()
    {
        $mail = $this->from(ConfigHelper::emailRemetente(), ConfigHelper::nomeRemetente())
                     ->subject('Novo Pedido Criado - ' . $this->pedido->nome_navio)
                     ->view('emails.novo-pedido')
                     ->with([
                         'pedido' => $this->pedido,
                         'nomeEmpresa' => ConfigHelper::nomeEmpresa(),
                         'logoEmpresa' => ConfigHelper::logoEmpresa()
                     ]);

        // Anexar documentos do pedido
        $this->anexarDocumentosAntigos($mail);

        return $mail;
    }

    private function anexarDocumentosAntigos($mail)
    {
        $camposDocumentos = [
            'certificados_navio',
            'declaracoes_cargas',
            'declaracao_provisoes_bordo',
            'declaracao_pertences_tripulacao',
            'documentos_tripulantes',
            'documentos_passageiros',
            'declaracao_mercadorias_perigosas'
        ];

        $totalAnexos = 0;
        $maxAnexos = 10; // Limite para evitar e-mails muito pesados

        Log::info("Iniciando anexação de documentos para pedido {$this->pedido->id}");

        foreach ($camposDocumentos as $campo) {
            $documentos = $this->pedido->$campo;
            
            if (is_array($documentos) && !empty($documentos)) {
                Log::info("Campo {$campo} tem " . count($documentos) . " documentos");
                
                foreach ($documentos as $caminhoArquivo) {
                    if ($totalAnexos >= $maxAnexos) {
                        Log::warning("Limite de anexos atingido para pedido {$this->pedido->id}");
                        break 2; // Sair dos dois loops
                    }

                    // O caminho já é uma string simples
                    if (is_string($caminhoArquivo) && !empty($caminhoArquivo)) {
                        try {
                            // Verificar se o arquivo existe
                            if (Storage::disk('private')->exists($caminhoArquivo)) {
                                $caminhoCompleto = Storage::disk('private')->path($caminhoArquivo);
                                $nomeArquivo = basename($caminhoArquivo);
                                
                                // Anexar o arquivo
                                $mail->attach($caminhoCompleto, [
                                    'as' => $nomeArquivo,
                                ]);
                                
                                $totalAnexos++;
                                Log::info("Anexo adicionado: {$nomeArquivo} para pedido {$this->pedido->id}");
                            } else {
                                Log::warning("Arquivo não encontrado: {$caminhoArquivo} para pedido {$this->pedido->id}");
                            }
                        } catch (\Exception $e) {
                            Log::error("Erro ao anexar arquivo {$caminhoArquivo}: " . $e->getMessage());
                        }
                    }
                }
            } else {
                Log::info("Campo {$campo} está vazio ou não é array");
            }
        }

        Log::info("Total de anexos adicionados: {$totalAnexos} para pedido {$this->pedido->id}");
    }
}