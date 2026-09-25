<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Politique de mots de passe unique pour toute l'application :
        // 10 caractères min., lettres + chiffres ; en production, refus des mots de
        // passe apparus dans des fuites connues (Have I Been Pwned, k-anonymat).
        Password::defaults(function () {
            $regle = Password::min(10)->letters()->numbers();
            return $this->app->isProduction() ? $regle->uncompromised() : $regle;
        });

        // Les liens générés (emails d'activation / de réinitialisation) utilisent toujours
        // APP_URL — hôte ET protocole —, jamais l'en-tête Host de la requête (faille E3).
        URL::forceRootUrl(config('app.url'));
        if ($this->app->isProduction() || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Rend l'utilisateur connecté ($u) disponible dans toutes les vues.
        View::composer('*', function ($view) {
            $view->with('u', auth()->user());
        });

        // (L'email de réinitialisation est personnalisé via
        //  User::sendPasswordResetNotification -> App\Notifications\ReinitialiserMotDePasse.)
    }
}
