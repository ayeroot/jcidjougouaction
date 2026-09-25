<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Envoyé à la NOUVELLE adresse : prouve que la personne la possède. */
class ConfirmationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $token) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Confirmez votre nouvelle adresse email — JCI Djougou Action');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.confirmation-email', with: [
            'url'    => route('email.confirmer', ['token' => $this->token]),
            'heures' => config('jci.email_confirmation_heures'),
        ]);
    }
}
