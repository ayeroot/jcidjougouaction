<?php

namespace App\Http\Controllers;

use App\Mail\AlerteChangementEmail;
use App\Mail\ConfirmationEmail;
use App\Models\Membre;
use App\Models\User;
use App\Support\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Changement d'email en libre-service, sécurisé :
 *  1. l'utilisateur saisit la nouvelle adresse ET son mot de passe actuel ;
 *  2. un lien part vers la NOUVELLE adresse (preuve qu'il la possède) ;
 *  3. l'ANCIENNE adresse est alertée (demande, puis changement effectif) ;
 *  4. l'email ne change qu'au clic sur le lien (valable 24 h, usage unique).
 */
class EmailController extends Controller
{
    public function demander(Request $request)
    {
        $user = $request->user();

        $data = $request->validateWithBag('email', [
            'nouvel_email' => ['required', 'email:rfc', 'max:255', Rule::notIn([$user->email]),
                Rule::unique('users', 'email'),
                Rule::unique('membres', 'email')->ignore($user->membre_id)],
            'actuel_email' => ['required'],
        ], [
            'nouvel_email.unique'    => 'Cette adresse est déjà utilisée.',
            'nouvel_email.not_in'    => "C'est déjà votre adresse actuelle.",
        ], ['nouvel_email' => 'nouvelle adresse', 'actuel_email' => 'mot de passe actuel']);

        if (! Hash::check($data['actuel_email'], $user->password)) {
            return back()->withErrors(['actuel_email' => 'Mot de passe actuel incorrect.'], 'email')->withInput();
        }

        $nouvel = Str::lower($data['nouvel_email']);
        $token  = Str::random(64);

        $user->forceFill([
            'email_en_attente'      => $nouvel,
            'email_token'           => hash('sha256', $token),
            'email_token_expire_at' => now()->addHours(config('jci.email_confirmation_heures')),
        ])->save();

        try {
            Mail::to($nouvel)->send(new ConfirmationEmail($user, $token));
            Mail::to($user->email)->send(new AlerteChangementEmail($user, $nouvel));
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['nouvel_email' => "L'email de confirmation n'a pas pu être envoyé. Réessayez plus tard."], 'email');
        }

        Journal::ecrire('EMAIL_DEMANDE', User::class, $user->id, ['email' => $user->email], ['email' => $nouvel]);

        return back()->with('ok', "Un lien de confirmation a été envoyé à {$nouvel}. Votre adresse ne changera qu'après avoir cliqué dessus.");
    }

    public function confirmer(Request $request, string $token)
    {
        $user = User::where('email_token', hash('sha256', $token))
            ->where('email_token_expire_at', '>', now())
            ->first();

        // Réponse : « Mon profil » si connecté ; sinon une page dédiée (le lien peut être
        // ouvert depuis un autre appareil, par exemple le téléphone).
        $retour = function (string $type, string $msg) {
            if (auth()->check()) {
                return $type === 'ok'
                    ? redirect()->route('profil.edit')->with('ok', $msg)
                    : redirect()->route('profil.edit')->withErrors(['nouvel_email' => $msg], 'email');
            }
            return response()->view('auth.email-confirmation', ['reussi' => $type === 'ok', 'message' => $msg]);
        };

        if (! $user || ! $user->email_en_attente) {
            return $retour('erreur', 'Ce lien de confirmation est invalide ou a expiré. Refaites la demande depuis « Mon profil ».');
        }

        $nouvel = $user->email_en_attente;

        // L'adresse a pu être prise entre-temps.
        $prise = User::where('email', $nouvel)->where('id', '!=', $user->id)->exists()
            || Membre::where('email', $nouvel)->where('id', '!=', $user->membre_id ?? 0)->exists();
        if ($prise) {
            $user->forceFill(['email_en_attente' => null, 'email_token' => null, 'email_token_expire_at' => null])->save();
            return $retour('erreur', 'Cette adresse est désormais utilisée par un autre compte.');
        }

        $ancien = $user->email;

        DB::transaction(function () use ($user, $nouvel, $ancien) {
            $user->forceFill([
                'email'                 => $nouvel,
                'email_verified_at'     => now(),
                'email_en_attente'      => null,
                'email_token'           => null,
                'email_token_expire_at' => null,
            ])->save();

            // La fiche membre suit (c'est elle qui sert pour le CDL et les activations).
            $user->membre?->update(['email' => $nouvel]);

            // Un éventuel lien « mot de passe oublié » envoyé à l'ancienne adresse ne vaut plus rien.
            DB::table(config('auth.passwords.users.table', 'password_reset_tokens'))->where('email', $ancien)->delete();
        });

        Journal::ecrire('EMAIL_CHANGE', User::class, $user->id, ['email' => $ancien], ['email' => $nouvel], $user);

        try {
            Mail::to($ancien)->send(new AlerteChangementEmail($user, $nouvel, confirme: true));
        } catch (\Throwable $e) {
            report($e);
        }

        return $retour('ok', "Adresse email mise à jour : {$nouvel}.");
    }
}
