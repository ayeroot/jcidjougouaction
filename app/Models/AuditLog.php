<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'role', 'action', 'auditable_type', 'auditable_id', 'modifications', 'old_values', 'new_values'];
    protected $casts = ['modifications' => 'array', 'old_values' => 'array', 'new_values' => 'array'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
