<?php
namespace App\Models;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Carriere extends Model
{
    use Auditable;
    protected $table = 'carrieres';
    protected $fillable = ['membre_id', 'type', 'annee', 'description'];
    public function membre(): BelongsTo { return $this->belongsTo(Membre::class); }
}
