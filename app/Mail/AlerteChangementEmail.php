<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Envoyé à l'ANCIENNE adresse : alerte si la demande n'est pas de son propriétaire. */
class AlerteChangementEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $nouvelEmail, public bool $confirme = false) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: ($this->confirme ? 'Votre adresse email a été modifiée' : 'Demande de changement de votre adresse email').' — JCI Djougou Action');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.alerte-changement-email');
    }
}
