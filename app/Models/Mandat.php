<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mandat extends Model
{
    protected $fillable = ['annee', 'theme', 'logo', 'couleur', 'photo_famille',
                           'date_debut', 'date_fin', 'actif'];
    protected $casts = ['date_debut' => 'date', 'date_fin' => 'date', 'actif' => 'boolean'];

    public function projets(): HasMany { return $this->hasMany(Projet::class); }
    public function formations(): HasMany { return $this->hasMany(Formation::class); }
    public function cotisations(): HasMany { return $this->hasMany(Cotisation::class); }

    public static function actif(): ?self { return static::where('actif', true)->first(); }

    /** Couleur du mandat (ou couleur aqua par défaut). */
    public function couleurOuDefaut(): string { return $this->couleur ?: '#0891b2'; }
}
