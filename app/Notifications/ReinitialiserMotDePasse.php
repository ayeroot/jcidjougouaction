<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReinitialiserMotDePasse extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Durée de validité (minutes) configurée pour le broker par défaut.
        $broker  = config('auth.defaults.passwords', 'users');
        $minutes = (int) config("auth.passwords.$broker.expire", 60);

        // URL absolue vers notre route française de réinitialisation.
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], absolute: false));

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe — JCI Djougou Action')
            ->view('emails.reset', [
                'url'     => $url,
                'user'    => $notifiable,
                'minutes' => $minutes,
            ]);
    }
}
