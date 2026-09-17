<?php
namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivationCompte extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $token) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Activez votre compte — JCI Djougou Action');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.activation', with: [
            'url' => route('activation.show', ['token' => $this->token]),
        ]);
    }
}
