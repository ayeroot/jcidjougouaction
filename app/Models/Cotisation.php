<?php
namespace App\Models;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cotisation extends Model
{
    use Auditable;
    protected $fillable = ['membre_id', 'mandat_id', 'montant', 'date_cotisation'];
    protected $casts = ['montant' => 'decimal:2', 'date_cotisation' => 'date'];
    public function membre(): BelongsTo { return $this->belongsTo(Membre::class); }
    public function mandat(): BelongsTo { return $this->belongsTo(Mandat::class); }
}
