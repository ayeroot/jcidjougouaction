<?php
namespace App\Models;
use App\Support\Auditable;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Auditable;
    use Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password', 'membre_id', 'actif', 'activation_token', 'activation_expire_at'];
    protected $hidden = ['password', 'remember_token', 'activation_token', 'email_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'actif'                => 'boolean',
            'activation_expire_at' => 'datetime',
            'email_token_expire_at' => 'datetime',
        ];
    }

    public function membre(): BelongsTo { return $this->belongsTo(Membre::class); }

    /** Un compte est activé quand il a un mot de passe défini et qu'il est actif. */
    public function estSuperAdmin(): bool
    {
        return $this->hasRole(\App\Support\Permissions::ROLE_SUPER);
    }

    /**
     * Ce compte peut-il utiliser la plateforme ? Tant que l'accès des membres est
     * fermé (config jci.acces_membres), un compte qui n'a QUE le rôle « membre »
     * (ou aucun rôle) ne peut pas se connecter.
     */
    public function aAccesPlateforme(): bool
    {
        if (config('jci.acces_membres')) {
            return true;
        }
        return $this->estSuperAdmin()
            || $this->hasAnyRole(\App\Support\Permissions::rolesAvecAcces());
    }

    public function estActive(): bool
    {
        return (bool) $this->actif && ! empty($this->password);
    }

    /** Email de réinitialisation personnalisé (français, aux couleurs de JCI). */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ReinitialiserMotDePasse($token));
    }
}
