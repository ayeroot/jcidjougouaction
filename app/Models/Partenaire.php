<?php
namespace App\Models;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partenaire extends Model
{
    use Auditable;
    protected $fillable = ['nom', 'type', 'contact', 'logo', 'public'];
    protected $casts = ['public' => 'boolean'];
    public function contributions(): HasMany { return $this->hasMany(Contribution::class); }
    public function scopePublics($q) { return $q->where('public', true); }
}
