<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\Journal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // N'authentifier que les comptes actifs.
        if (Auth::attempt($credentials + ['actif' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();
            Journal::ecrire('LOGIN', 'Auth', $request->user()->id, [], ['ip' => $request->ip()]);
            return redirect()->intended(route('dashboard'));
        }

        // Faible (énumération) — le message « compte désactivé » n'est affiché qu'à
        // quelqu'un qui connaît déjà le bon mot de passe ; sinon, message générique.
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        Journal::ecrire('LOGIN_ECHEC', 'Auth', $user?->id, [], [
            'email' => $credentials['email'], 'ip' => $request->ip(),
        ], $user);

        if ($user && ! $user->actif && Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => "Ce compte est désactivé. Contactez l'administrateur.",
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => "Identifiants incorrects.",
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
