<?php

namespace App\Mail;

use App\Models\Incidente;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper;
use Illuminate\Support\Facades\Log;

class NovoIncidenteNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $incidente;

    public function __construct(Incidente $incidente)
    {
        $this->incidente = $incidente;
    }

    public function build()
    {
        // Definir prioridade do e-mail baseada na gravidade
        $prioridade = $this->getPrioridadeEmail($this->incidente->gravidade);
        
        $mail = $this->from(ConfigHelper::emailRemetente(), ConfigHelper::nomeRemetente())
                     ->subject('🚨 Novo Incidente Registrado - ' . $this->incidente->titulo)
                     ->view('emails.novo-incidente')
                     ->with([
                         'incidente' => $this->incidente,
                         'nomeEmpresa' => ConfigHelper::nomeEmpresa(),
                         'logoEmpresa' => ConfigHelper::logoEmpresa(),
                         'prioridade' => $prioridade
                     ]);

        // Definir prioridade do e-mail
        if ($prioridade === 'alta') {
            $mail->priority(1); // Alta prioridade
        } elseif ($prioridade === 'media') {
            $mail->priority(3); // Prioridade normal
        } else {
            $mail->priority(5); // Baixa prioridade
        }

        return $mail;
    }

    /**
     * Determina a prioridade do e-mail baseada na gravidade do incidente
     */
    private function getPrioridadeEmail($gravidade)
    {
        switch ($gravidade) {
            case 'critica':
            case 'alta':
                return 'alta';
            case 'media':
                return 'media';
            case 'baixa':
            default:
                return 'baixa';
        }
    }
}