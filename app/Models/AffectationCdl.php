<?php
namespace App\Models;
use App\Support\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffectationCdl extends Model
{
    use Auditable;

    protected $table = 'affectations_cdl';
    protected $fillable = ['mandat_id', 'membre_id', 'poste'];

    /**
     * Correspondance poste du CDL -> rôle applicatif.
     * Les libellés de poste sont ceux de Membre::FONCTIONS.
     */
    public const POSTE_ROLE = [
        'Président Local'                => 'president',
        'VP Exécutive'                   => 'vpe',
        'VP Relations Extérieures'       => 'vpre',
        'VP Formations'                  => 'vpf',
        'VP Management'                  => 'vpm',
        'VP Croissance & Développement'  => 'vpcd',
        'VP Projet & Thème principal'    => 'vp_projet',
        'Trésorier Général'              => 'tresorier',
        'Secrétaire Général'             => 'secretaire',
    ];

    /** Postes affectables (exclut « Membre » qui n'est pas un poste du bureau). */
    public static function postes(): array
    {
        return array_keys(self::POSTE_ROLE);
    }

    public function mandat(): BelongsTo { return $this->belongsTo(Mandat::class); }
    public function membre(): BelongsTo { return $this->belongsTo(Membre::class); }

    public function role(): string
    {
        return self::POSTE_ROLE[$this->poste] ?? 'membre';
    }
}
