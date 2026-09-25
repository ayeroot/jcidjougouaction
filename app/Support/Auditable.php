<?php
namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Trait de traçabilité : journalise automatiquement les créations (CREATE),
 * modifications (UPDATE) et suppressions (DELETE) du modèle dans audit_logs.
 * Les lectures (READ) ne sont jamais enregistrées.
 *
 * L'audit est déclenché par les événements Eloquent (côté backend) : il ne peut
 * donc pas être contourné depuis le frontend.
 */
trait Auditable
{
    /** Champs jamais journalisés (sécurité / confidentialité). */
    protected array $auditExclude = [
        'password', 'remember_token', 'activation_token', 'activation_expire_at',
        'email_token', 'email_token_expire_at',
        'updated_at', 'created_at',
    ];

    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->enregistrerAudit('CREATE', [], $model->auditFiltre($model->getAttributes()));
        });

        static::updated(function ($model) {
            $changes = $model->auditFiltre($model->getChanges());
            if (empty($changes)) return; // rien de significatif n'a changé
            $anciennes = [];
            foreach (array_keys($changes) as $champ) {
                $anciennes[$champ] = $model->getOriginal($champ);
            }
            $model->enregistrerAudit('UPDATE', $anciennes, $changes);
        });

        static::deleted(function ($model) {
            $model->enregistrerAudit('DELETE', $model->auditFiltre($model->getOriginal()), []);
        });
    }

    /** Retire les champs sensibles/techniques du jeu de valeurs. */
    protected function auditFiltre(array $valeurs): array
    {
        return collect($valeurs)->except($this->auditExclude)->all();
    }

    protected function enregistrerAudit(string $action, array $anciennes, array $nouvelles): void
    {
        $user = Auth::user();
        AuditLog::create([
            'user_id'        => $user?->id,
            'role'           => $user?->getRoleNames()->first(),
            'action'         => $action,
            'auditable_type' => static::class,
            'auditable_id'   => $this->getKey(),
            'modifications'  => $nouvelles,          // conservé pour compatibilité
            'old_values'     => $anciennes ?: null,
            'new_values'     => $nouvelles ?: null,
        ]);
    }
}
