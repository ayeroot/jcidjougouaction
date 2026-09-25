<?php

return [
    /*
    | Accès des simples membres à la plateforme.
    | false (par défaut) : seuls le super administrateur, l'administrateur et les
    | membres affectés à un poste du CDL ont un compte et peuvent se connecter.
    | Les membres simples et les candidats ne reçoivent aucun email ni compte.
    | Passer JCI_ACCES_MEMBRES=true le jour où l'espace membre est ouvert à tous.
    */
    'acces_membres' => (bool) env('JCI_ACCES_MEMBRES', false),

    /*
    | Proxies de confiance (Cloudflare, répartiteur de charge…) : « * » ou liste d'IP
    | séparées par des virgules. À laisser VIDE sur un VPS où Nginx reçoit directement
    | les visiteurs. Lu ici (et non dans bootstrap/app.php) pour rester valable avec
    | « php artisan config:cache ».
    */
    'trusted_proxies' => env('TRUSTED_PROXIES'),

    /* Durée de validité du lien de confirmation d'un changement d'email (heures). */
    'email_confirmation_heures' => 24,
];
