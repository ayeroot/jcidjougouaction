<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // N'accepte que l'hôte défini dans APP_URL (bloque l'empoisonnement via l'en-tête Host).
        // Les motifs sont des expressions régulières : l'hôte est échappé ET ancré (^…$),
        // sinon « jci.bj » accepterait aussi « jci.bj.attaquant.com ».
        $middleware->trustHosts(at: static function () {
            $url  = (string) config('app.url');
            $host = parse_url(str_contains($url, '://') ? $url : 'https://'.$url, PHP_URL_HOST);
            return $host ? ['^'.preg_quote($host).'$'] : [];
        }, subdomains: false);

        // En-têtes de sécurité HTTP (CSP, anti-clickjacking, HSTS…) — faille M7.
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Alias des middlewares de gestion des rôles (spatie/laravel-permission)
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Déconnecte tout compte désactivé, même session déjà ouverte (faille E1).
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureCompteActif::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
