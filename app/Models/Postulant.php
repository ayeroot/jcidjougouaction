<?php
namespace App\Models;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Postulant extends Model
{
    use Auditable;

    public const STATUTS = ['nouveau', 'contacte', 'en_formation', 'examen', 'admis', 'rejete'];

    protected $fillable = [
        'nom', 'prenom', 'date_naissance', 'sexe', 'email', 'telephone',
        'ville', 'motivation', 'statut', 'membre_id',
    ];
    protected $casts = ['date_naissance' => 'date'];

    public function membre(): BelongsTo { return $this->belongsTo(Membre::class); }

    public function formations(): BelongsToMany
    {
        return $this->belongsToMany(Formation::class, 'presences')
                    ->withPivot('present')->withTimestamps();
    }

    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    /** Nombre de formations réellement suivies (présent). */
    public function nombreFormationsSuivies(): int
    {
        return $this->formations()->wherePivot('present', true)->count();
    }
}
