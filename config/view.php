<?php
return [
    'paths' => [
        resource_path('views'),
    ],
    // Sur Vercel : VIEW_COMPILED_PATH=/tmp/views (le seul dossier écrivable)
    'compiled' => env('VIEW_COMPILED_PATH', realpath(storage_path('framework/views'))),
];
