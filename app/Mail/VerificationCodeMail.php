<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable {
    use Queueable, SerializesModels;

    public $codigo;

    public function __construct($codigo) {
        $this->codigo = $codigo;
    }

    public function build() {
        return $this->subject('Código de Verificación')
                    ->view('verification')
                    ->with(['codigo' => $this->codigo]);
    }
}
