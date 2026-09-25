<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Journal d'audit pour les événements qui ne passent pas par un modèle Auditable :
 * connexions, changements de rôles / permissions (tables pivot Spatie), etc.
 */
class Journal
{
    public static function ecrire(string $action, string $type, $id = null, array $avant = [], array $apres = [], $user = null): void
    {
        $user ??= Auth::user();

        try {
            AuditLog::create([
                'user_id'        => $user?->id,
                'role'           => $user?->getRoleNames()->first(),
                'action'         => $action,
                'auditable_type' => $type,
                'auditable_id'   => $id ?? 0,
                'modifications'  => $apres,
                'old_values'     => $avant ?: null,
                'new_values'     => $apres ?: null,
            ]);
        } catch (\Throwable $e) {
            report($e); // le journal ne doit jamais bloquer l'action
        }
    }
}
