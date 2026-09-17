<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Refuser un compte désactivé (règle CDL : un seul compte actif par poste).
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        if ($user && ! $user->actif) {
            return back()->withErrors([
                'email' => "Ce compte est désactivé. Contactez l'administrateur.",
            ])->onlyInput('email');
        }

        // N'authentifier que les comptes actifs.
        if (Auth::attempt($credentials + ['actif' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
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
