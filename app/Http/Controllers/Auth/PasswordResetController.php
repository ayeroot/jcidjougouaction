<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    /** Formulaire « mot de passe oublié ». */
    public function demande()
    {
        return view('auth.mot-de-passe-oubli');
    }

    /** Envoie le lien de réinitialisation (token sécurisé, à durée limitée). */
    public function envoyer(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // Password broker : token haché en base, expiration gérée par config auth.passwords.
        $statut = Password::sendResetLink($request->only('email'));

        // Message neutre pour ne pas révéler l'existence d'un compte.
        return back()->with('ok', "Si un compte existe pour cette adresse, un email de réinitialisation vient d'être envoyé.");
    }

    /** Formulaire de nouveau mot de passe (depuis le lien de l'email). */
    public function formulaire(Request $request, string $token)
    {
        return view('auth.reinitialiser', ['token' => $token, 'email' => $request->query('email')]);
    }

    /** Enregistre le nouveau mot de passe et invalide le token. */
    public function reinitialiser(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $statut = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                    'actif'          => true,
                ])->save();
            }
        );

        if ($statut === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('ok', 'Mot de passe réinitialisé. Vous pouvez vous connecter.');
        }

        return back()->withErrors(['email' => __($statut)]);
    }
}
