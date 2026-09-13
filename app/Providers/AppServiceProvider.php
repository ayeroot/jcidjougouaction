<?php

namespace App\Providers;

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
        // Rend l'utilisateur connecté ($u) disponible dans toutes les vues.
        View::composer('*', function ($view) {
            $view->with('u', auth()->user());
        });
    }
}
