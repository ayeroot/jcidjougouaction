<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * M7 — En-têtes de sécurité HTTP sur toutes les réponses.
 *
 * CSP : tout vient du site lui-même ('self'). 'unsafe-inline' reste autorisé pour
 * les scripts et styles car les vues utilisent des onclick="..." et style="..." ;
 * l'essentiel est gagné : aucun script externe ne peut être chargé ni exécuté.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $h = $response->headers;
        $h->set('X-Frame-Options', 'DENY');                       // anti-clickjacking
        $h->set('X-Content-Type-Options', 'nosniff');
        $h->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $h->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $h->set('Cross-Origin-Opener-Policy', 'same-origin');

        if ($request->isSecure()) {
            $h->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // En local avec « npm run dev », le serveur Vite sert les assets : pas de CSP.
        if (! app()->environment('local')) {
            $h->set('Content-Security-Policy', $this->csp());
        }

        return $response;
    }

    private function csp(): string
    {
        // Images : le site + le stockage externe éventuel (S3 / R2 sur Vercel).
        $images = ["'self'", 'data:', 'blob:'];
        if ($s3 = config('filesystems.disks.s3.url')) {
            $parts = parse_url($s3);
            if (! empty($parts['host'])) {
                $images[] = ($parts['scheme'] ?? 'https').'://'.$parts['host'];
            }
        }

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline'",
            'img-src '.implode(' ', $images),
            "font-src 'self' data:",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]);
    }
}
