<?php
namespace App\Models;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Formation extends Model
{
    use Auditable;

    /** Cycle de vie d'une formation. */
    public const STATUTS = [
        'planifiee'      => 'Planifiée',
        'en_cours'       => 'En cours',
        'terminee'       => 'Terminée',
        'rapport_soumis' => 'Rapport soumis',
        'annulee'        => 'Annulée',
    ];

    protected $fillable = [
        'titre', 'theme', 'objectifs', 'date_formation', 'lieu',
        'statut', 'mandat_id', 'formateur_id',
    ];
    protected $casts = ['date_formation' => 'datetime'];

    /** La formation est-elle terminée (date passée ou statut terminé/soumis) ? */
    public function estTerminee(): bool
    {
        if (in_array($this->statut, ['terminee', 'rapport_soumis'], true)) return true;
        return $this->date_formation && $this->date_formation->isPast();
    }

    /** Le rapport ne peut être soumis qu'une fois la formation terminée. */
    public function peutRecevoirRapport(): bool
    {
        return $this->estTerminee() && $this->statut !== 'annulee';
    }

    public function mandat(): BelongsTo { return $this->belongsTo(Mandat::class); }
    public function formateur(): BelongsTo { return $this->belongsTo(Formateur::class); }
    public function rapport(): HasOne { return $this->hasOne(RapportFormation::class); }

    public function postulants(): BelongsToMany
    {
        return $this->belongsToMany(Postulant::class, 'presences')
                    ->withPivot('present')->withTimestamps();
    }

    public function presents(): BelongsToMany
    {
        return $this->postulants()->wherePivot('present', true);
    }

    public function scopeDuMois($q, int $annee, int $mois) {
        return $q->whereYear('date_formation', $annee)->whereMonth('date_formation', $mois);
    }
}
