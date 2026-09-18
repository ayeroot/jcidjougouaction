<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['cle', 'valeur'];
    public $timestamps = true;

    /** Récupère une valeur de configuration (avec valeur par défaut). */
    public static function get(string $cle, $defaut = null)
    {
        return static::tousLesReglages()[$cle] ?? $defaut;
    }

    /** Enregistre une valeur de configuration. */
    public static function put(string $cle, $valeur): void
    {
        static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        Cache::forget('settings.all');
    }

    /** Tous les réglages sous forme de tableau clé => valeur (mis en cache). */
    public static function tousLesReglages(): array
    {
        return Cache::rememberForever('settings.all', fn () => static::pluck('valeur', 'cle')->all());
    }
}
