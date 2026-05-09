<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $dateTime;
    public $device;
    public $ip;

    public function __construct($dateTime, $device, $ip)
    {
        $this->dateTime = $dateTime;
        $this->device = $device;
        $this->ip = $ip;
    }

    public function build()
    {
        return $this
            ->subject('Nuevo inicio de sesión detectado')
            ->view('mail.alert');
    }
}
