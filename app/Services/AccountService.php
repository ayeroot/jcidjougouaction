<?php
namespace App\Services;

use App\Mail\ActivationCompte;
use App\Models\Membre;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Service centralisant la gestion des comptes utilisateurs :
 * création à partir d'une fiche membre, activation par email,
 * et règles d'unicité/désactivation liées aux postes du CDL.
 *
 * Toutes les règles sont appliquées côté backend (non contournables depuis le frontend).
 */
class AccountService
{
    public const ACTIVATION_HEURES = 48;

    /**
     * Crée (ou réactive) un compte pour un membre existant et lui attribue un rôle.
     * Les informations personnelles proviennent de la fiche membre (aucune ressaisie).
     *
     * @return array{user: User, email_envoye: bool, message: ?string}
     */
    public function creerPourMembre(Membre $membre, string $role): array
    {
        if (empty($membre->email)) {
            return ['user' => null, 'email_envoye' => false,
                    'message' => "Le membre n'a pas d'adresse email : renseignez-la sur sa fiche avant de créer le compte."];
        }

        // Un membre = au plus un compte (pas de doublon).
        $user = User::where('membre_id', $membre->id)->orWhere('email', $membre->email)->first();

        if (! $user) {
            $user = new User([
                'name'      => $membre->nom_complet ?: $membre->fonction,
                'email'     => $membre->email,
                'membre_id' => $membre->id,
            ]);
            // Mot de passe provisoire aléatoire (inutilisable) ; défini par le membre à l'activation.
            $user->password = Hash::make(Str::random(40));
            $user->actif = false;
            $user->save();
        }

        $user->syncRoles([$role]);

        // Poste du CDL : un seul compte actif pour ce rôle -> désactiver les anciens.
        $this->desactiverAnciensDuRole($role, $user->id);

        $envoye = $this->envoyerActivation($user);

        return ['user' => $user, 'email_envoye' => $envoye, 'message' => null];
    }

    /** Génère un token d'activation à durée limitée et envoie l'email. */
    public function envoyerActivation(User $user): bool
    {
        $token = Str::random(64);
        $user->forceFill([
            'activation_token'     => hash('sha256', $token),
            'activation_expire_at' => now()->addHours(self::ACTIVATION_HEURES),
        ])->save();

        try {
            Mail::to($user->email)->send(new ActivationCompte($user, $token));
            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    /**
     * Désactive les autres comptes portant le même rôle (règle : un seul actif par poste).
     * Ne touche pas au rôle « membre » ni « admin ».
     */
    public function desactiverAnciensDuRole(string $role, int $sauf): void
    {
        if (in_array($role, ['membre', 'admin'], true)) return;

        User::role($role)->where('id', '!=', $sauf)->where('actif', true)
            ->get()->each(function (User $autre) {
                $autre->forceFill(['actif' => false])->save();
            });
    }

    /** Active le compte après définition du mot de passe. */
    public function activer(User $user, string $motDePasse): void
    {
        $user->forceFill([
            'password'             => Hash::make($motDePasse),
            'actif'                => true,
            'activation_token'     => null,
            'activation_expire_at' => null,
            'email_verified_at'    => now(),
        ])->save();

        // À l'activation, si ce compte porte un rôle de poste, désactiver les anciens.
        $role = $user->getRoleNames()->first();
        if ($role) {
            $this->desactiverAnciensDuRole($role, $user->id);
        }
    }

    /** Retrouve un compte par token d'activation valide (non expiré). */
    public function parToken(string $token): ?User
    {
        return User::where('activation_token', hash('sha256', $token))
            ->where('activation_expire_at', '>', now())
            ->first();
    }
}
