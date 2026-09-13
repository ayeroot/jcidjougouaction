<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    protected $fillable = ['libelle', 'montant', 'date_depense', 'projet_id', 'mandat_id'];
    protected $casts = ['montant' => 'decimal:2', 'date_depense' => 'date'];
    public function projet(): BelongsTo { return $this->belongsTo(Projet::class); }
}
