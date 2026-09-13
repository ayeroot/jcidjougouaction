<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    protected $fillable = ['formation_id', 'postulant_id', 'present'];
    protected $casts = ['present' => 'boolean'];
}
