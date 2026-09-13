<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contribution extends Model
{
    protected $fillable = ['source', 'montant', 'date_contribution', 'partenaire_id', 'projet_id'];
    protected $casts = ['montant' => 'decimal:2', 'date_contribution' => 'date'];
    public function partenaire(): BelongsTo { return $this->belongsTo(Partenaire::class); }
    public function projet(): BelongsTo { return $this->belongsTo(Projet::class); }
}
