<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Archive extends Model
{
    public const TYPES = ['rapport', 'photo', 'document', 'autre'];
    protected $fillable = ['titre', 'type', 'description', 'reference', 'date_document', 'mandat_id'];
    protected $casts = ['date_document' => 'date'];
    public function mandat(): BelongsTo { return $this->belongsTo(Mandat::class); }
}
