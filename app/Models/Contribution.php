<?php
namespace App\Models;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contribution extends Model
{
    use Auditable;

    /** Origines possibles d'un financement (hors cotisation annuelle). */
    public const TYPES = [
        'participation_membre' => 'Participation d’un membre',
        'contribution_membre'  => 'Contribution d’un membre',
        'don_particulier'      => 'Don (particulier)',
        'don_partenaire'       => 'Don d’un partenaire',
        'autre'                => 'Autre',
    ];

    protected $fillable = ['type', 'source', 'donateur', 'montant', 'date_contribution', 'membre_id', 'partenaire_id', 'projet_id'];
    protected $casts = ['montant' => 'decimal:2', 'date_contribution' => 'date'];

    public function membre(): BelongsTo { return $this->belongsTo(Membre::class); }
    public function partenaire(): BelongsTo { return $this->belongsTo(Partenaire::class); }
    public function projet(): BelongsTo { return $this->belongsTo(Projet::class); }

    /** Libellé lisible de l'origine du financement. */
    public function getOrigineAttribute(): string
    {
        return match ($this->type) {
            'participation_membre', 'contribution_membre' => $this->membre?->nom_complet ?? 'Membre',
            'don_partenaire'  => $this->partenaire?->nom ?? 'Partenaire',
            'don_particulier' => $this->donateur ?: 'Donateur',
            default           => $this->source ?: '—',
        };
    }
}