<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForgetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetLink;
    public $userEmail;

    public function __construct($resetLink, $userEmail)
    {
        $this->resetLink = $resetLink;
        $this->userEmail = $userEmail;
    }

    public function build()
    {
        return $this->subject('¿Olvidaste tu contraseña?')
            ->view('mail.forget');
    }
}
