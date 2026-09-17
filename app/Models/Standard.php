<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Standard extends Model
{
    protected $fillable = ['libelle', 'categorie', 'atteint', 'mandat_id'];
    protected $casts = ['atteint' => 'boolean'];
    public function mandat(): BelongsTo { return $this->belongsTo(Mandat::class); }
}
