<?php
namespace App\Models;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Membre extends Model
{
    use Auditable;

    /** Fonctions du CDL (liste déroulante — évite les fautes de saisie). */
    public const FONCTIONS = [
        'Président Local',
        'VP Exécutive',
        'Secrétaire Général',
        'Trésorier Général',
        'VP Affaires et Entrepreneuriat',
        'VP Communications et Marketing',
        'VP Croissance & Développement',
        'VP Formations',
        'VP Management',
        'VP Projet & Thème principal',
        'VP Relations Extérieures',
        'Auditeurs Généraux',
        'Membre',
    ];

    /** Montant annuel attendu de cotisation (pour le paiement échelonné). */
    public const COTISATION_ATTENDUE = 15000;

    protected $fillable = [
        'nom', 'prenom', 'date_naissance', 'sexe', 'email', 'telephone',
        'photo', 'fonction', 'promotion', 'ville', 'adresse', 'profession','statut', 'date_adhesion',
    ];
    protected $casts = ['date_naissance' => 'date', 'date_adhesion' => 'date'];

    public function user(): HasOne { return $this->hasOne(User::class); }
    public function carrieres(): HasMany { return $this->hasMany(Carriere::class); }
    public function cotisations(): HasMany { return $this->hasMany(Cotisation::class); }

    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_naissance?->age;
    }

    /** Statut « honorable » calculé : à jour de cotisation pour le mandat actif. */
    /** Total cotisé pour un mandat (somme des tranches). */
    public function montantCotise(?Mandat $mandat = null): float
    {
        $mandat = $mandat ?? Mandat::actif();
        if (! $mandat) return 0;
        return (float) $this->cotisations()->where('mandat_id', $mandat->id)->sum('montant');
    }

    /** Honorable = cotisation entièrement réglée (cumul >= montant attendu). */
    public function estHonorable(?Mandat $mandat = null): bool
    {
        return $this->montantCotise($mandat) >= self::COTISATION_ATTENDUE;
    }

    public function scopeMoinsDe40($q) {
        return $q->whereDate('date_naissance', '>', now()->subYears(40));
    }

    /** Membres dont l'anniversaire tombe dans un mois donné (1-12). */
    public function scopeAnniversaireMois($q, int $mois) {
        return $q->whereNotNull('date_naissance')->whereMonth('date_naissance', $mois);
    }

    /** Âge que le membre atteindra à son anniversaire de l'année en cours. */
    public function getAgeAnniversaireAttribute(): ?int
    {
        return $this->date_naissance ? now()->year - $this->date_naissance->year : null;
    }
}
