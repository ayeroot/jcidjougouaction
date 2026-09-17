<?php
namespace App\Models;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Projet extends Model
{
    use Auditable;
    protected $fillable = [
        'titre', 'description', 'statut', 'avancement',
        'public', 'mandat_id', 'responsable_id',
    ];
    protected $casts = ['public' => 'boolean'];

    public function mandat(): BelongsTo { return $this->belongsTo(Mandat::class); }
    public function responsable(): BelongsTo { return $this->belongsTo(Membre::class, 'responsable_id'); }
    public function contributions(): HasMany { return $this->hasMany(Contribution::class); }
    public function depenses(): HasMany { return $this->hasMany(Depense::class); }

    public function scopePublics($q) { return $q->where('public', true); }
}
