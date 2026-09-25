<?php
namespace App\Models;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mandat extends Model
{
    use Auditable;
    protected $fillable = ['annee', 'theme', 'logo', 'couleur', 'photo_famille',
                           'date_debut', 'date_fin', 'actif'];
    protected $casts = ['date_debut' => 'date', 'date_fin' => 'date', 'actif' => 'boolean'];

    public function projets(): HasMany { return $this->hasMany(Projet::class); }
    public function formations(): HasMany { return $this->hasMany(Formation::class); }
    public function cotisations(): HasMany { return $this->hasMany(Cotisation::class); }
    public function affectations(): HasMany { return $this->hasMany(AffectationCdl::class); }

    public static function actif(): ?self { return static::where('actif', true)->first(); }

    /** Couleur du mandat (ou couleur aqua par défaut). */
    public function couleurOuDefaut(): string
    {
        // Défense en profondeur : seule une couleur hexadécimale valide atteint l'attribut style.
        return preg_match('/^#[0-9a-fA-F]{6}$/', (string) $this->couleur) ? $this->couleur : '#0891b2';
    }
}
