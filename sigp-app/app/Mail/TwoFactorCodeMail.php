<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $userName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $code, string $userName = null)
    {
        $this->code = $code;
        $this->userName = $userName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Código de Verificação 2FA - ' . config('app.name'))
                    ->view('emails.two-factor-code')
                    ->with([
                        'code' => $this->code,
                        'userName' => $this->userName,
                        'appName' => config('app.name'),
                        'expiresIn' => '10 minutos'
                    ]);
    }
}