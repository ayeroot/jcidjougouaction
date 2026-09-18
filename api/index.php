<?php
/**
 * Point d'entrée Vercel (serverless).
 * Vercel a un système de fichiers en LECTURE SEULE, sauf /tmp.
 * On y prépare les dossiers écrivables et on y copie la base SQLite de démo.
 *
 * ⚠️ Démo/aperçu uniquement : les données de /tmp NE PERSISTENT PAS
 *    (réinitialisées quand l'instance est recyclée).
 */

// Dossier des vues Blade compilées (défini par VIEW_COMPILED_PATH sur Vercel)
@mkdir('/tmp/views', 0777, true);

// Base SQLite : on copie le modèle pré-rempli vers /tmp (écrivable) au démarrage
if (! file_exists('/tmp/database.sqlite')) {
    @copy(__DIR__ . '/../database/base_demo.db', '/tmp/database.sqlite');
}

require __DIR__ . '/../public/index.php';
