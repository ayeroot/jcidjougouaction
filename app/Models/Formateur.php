<?php
namespace App\Models;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Formateur extends Model
{
    use Auditable;
    protected $fillable = ['nom', 'type', 'membre_id', 'contact'];
    public function membre(): BelongsTo { return $this->belongsTo(Membre::class); }
    public function formations(): HasMany { return $this->hasMany(Formation::class); }
}
