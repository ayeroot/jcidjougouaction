<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Formation extends Model
{
    protected $fillable = [
        'titre', 'theme', 'objectifs', 'date_formation', 'lieu',
        'statut', 'mandat_id', 'formateur_id',
    ];
    protected $casts = ['date_formation' => 'datetime'];

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
