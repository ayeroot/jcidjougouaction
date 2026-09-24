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
        $middleware->trustHosts(at: static fn () => array_filter([
            parse_url(config('app.url'), PHP_URL_HOST),
        ]));

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
