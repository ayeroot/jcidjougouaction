<?php

namespace App\Support;

use App\Models\Mandat;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * Logo affiché dans les menus et footers.
 * Priorité : logo du site vitrine (Administration → Site vitrine),
 * sinon logo du mandat actif, sinon rien (le badge « JCI » s'affiche).
 */
class Logo
{
    public static function url(): ?string
    {
        return once(function () {
            $candidats = [
                Setting::get('vitrine_logo'),
                rescue(fn () => Mandat::actif()?->logo, null, false),
            ];

            foreach ($candidats as $chemin) {
                if ($chemin && Storage::disk('public')->exists($chemin)) {
                    // Chemin relatif : fonctionne quelle que soit l'adresse du site
                    // (127.0.0.1:8000 en local, domaine réel en production).
                    return '/storage/'.ltrim($chemin, '/');
                }
            }

            return null;
        });
    }
}
