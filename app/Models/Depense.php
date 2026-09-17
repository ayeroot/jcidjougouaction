<?php
namespace App\Models;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    use Auditable;
    public const CATEGORIES = [
        'projet'        => 'Projet',
        'prestation'    => 'Prestation (graphiste, …)',
        'secretariat'   => 'Secrétariat',
        'fonctionnement'=> 'Fonctionnement',
        'autre'         => 'Autre',
    ];

    protected $fillable = ['libelle', 'categorie', 'montant', 'date_depense', 'projet_id', 'mandat_id'];
    protected $casts = ['montant' => 'decimal:2', 'date_depense' => 'date'];
    public function projet(): BelongsTo { return $this->belongsTo(Projet::class); }
}
