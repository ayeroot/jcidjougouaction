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
        'VP Relations Extérieures',
        'VP Formations',
        'VP Management',
        'VP Croissance & Développement',
        'VP Projet & Thème principal',
        'Trésorier Général',
        'Secrétaire Général',
        'Membre',
    ];

    protected $fillable = [
        'nom', 'prenom', 'date_naissance', 'sexe', 'email', 'telephone',
        'photo', 'fonction', 'ville', 'adresse', 'statut', 'date_adhesion',
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
    public function estHonorable(?Mandat $mandat = null): bool
    {
        $mandat = $mandat ?? Mandat::actif();
        if (! $mandat) return false;
        return $this->cotisations()->where('mandat_id', $mandat->id)->exists();
    }

    public function scopeMoinsDe40($q) {
        return $q->whereDate('date_naissance', '>', now()->subYears(40));
    }
}
