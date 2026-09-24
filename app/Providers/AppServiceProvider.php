<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
            // Les liens générés (emails d'activation / de réinitialisation) utilisent toujours
        // APP_URL, jamais l'en-tête Host de la requête (faille E3).
        URL::forceRootUrl(config('app.url'));
        if ($this->app->isProduction()) {
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
