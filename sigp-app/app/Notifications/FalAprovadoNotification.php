<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class FalAprovadoNotification extends Notification
{
    use Queueable;

    protected $pedido;
    protected $falNumber;

    public function __construct($pedido, $falNumber)
    {
        $this->pedido = $pedido;
        $this->falNumber = $falNumber;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("FAL{$this->falNumber} Aprovado")
            ->line("O FAL{$this->falNumber} do pedido {$this->pedido->numero_pedido} foi aprovado.")
            ->action('Ver Pedido', route('janela-unica.show', $this->pedido))
            ->line('Obrigado por usar nosso sistema!');
    }
}