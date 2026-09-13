<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RapportFormation extends Model
{
    protected $fillable = ['formation_id', 'contenu', 'redige_par'];
    public function formation(): BelongsTo { return $this->belongsTo(Formation::class); }
    public function auteur(): BelongsTo { return $this->belongsTo(User::class, 'redige_par'); }
}
