<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompteActif
{
    /**
     * Déconnecte immédiatement tout utilisateur dont le compte a été désactivé,
     * même si sa session (ou son cookie « se souvenir de moi ») est encore ouverte.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->actif) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Votre compte a été désactivé.']);
        }

        return $next($request);
    }
}