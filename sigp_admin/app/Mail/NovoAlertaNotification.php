<?php

namespace App\Mail;

use App\Models\Alerta;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NovoAlertaNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $alerta;

    /**
     * Create a new message instance.
     */
    public function __construct(Alerta $alerta)
    {
        $this->alerta = $alerta;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'Novo Alerta: ' . $this->alerta->titulo;
        
        // Definir prioridade do e-mail baseada no nível do alerta
        $priority = match($this->alerta->nivel) {
            'emergencia' => 1, // Mais alta
            'critico' => 2,
            'aviso' => 3,
            'info' => 4,
            default => 3
        };

        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject($subject)
                    ->priority($priority)
                    ->view('emails.novo-alerta')
                    ->with([
                        'alerta' => $this->alerta,
                        'nomeEmpresa' => app('config.helper')->nomeEmpresa(),
                        'nomeDoSistema' => app('config.helper')->nomeDoSistema(),
                        'logoEmpresa' => app('config.helper')->logoEmpresa(),
                    ]);
    }
}