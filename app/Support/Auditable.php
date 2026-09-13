<?php
namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Trait de traçabilité : journalise automatiquement les créations,
 * modifications et suppressions du modèle dans la table audit_logs.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn ($model) => $model->enregistrerAudit('created', $model->getAttributes()));
        static::updated(fn ($model) => $model->enregistrerAudit('updated', $model->getChanges()));
        static::deleted(fn ($model) => $model->enregistrerAudit('deleted', ['id' => $model->getKey()]));
    }

    protected function enregistrerAudit(string $action, array $modifications): void
    {
        AuditLog::create([
            'user_id'        => Auth::id(),
            'action'         => $action,
            'auditable_type' => static::class,
            'auditable_id'   => $this->getKey(),
            'modifications'  => $modifications,
        ]);
    }
}
