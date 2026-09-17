<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanAction extends Model
{
    public const STATUTS = ['a_faire' => 'À faire', 'en_cours' => 'En cours', 'fait' => 'Fait'];

    protected $table = 'plan_actions';
    protected $fillable = ['titre', 'description', 'mois', 'statut', 'mandat_id'];
    public function mandat(): BelongsTo { return $this->belongsTo(Mandat::class); }
}
