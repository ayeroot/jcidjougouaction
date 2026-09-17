<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivationController extends Controller
{
    public function __construct(private AccountService $comptes) {}

    /** Affiche le formulaire de définition du mot de passe (via le lien reçu par email). */
    public function show(string $token)
    {
        $user = $this->comptes->parToken($token);
        if (! $user) {
            return view('auth.activation-invalide');
        }
        return view('auth.activation', ['token' => $token, 'user' => $user]);
    }

    /** Enregistre le mot de passe et active le compte. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'token'    => ['required', 'string'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = $this->comptes->parToken($data['token']);
        if (! $user) {
            return view('auth.activation-invalide');
        }

        $this->comptes->activer($user, $data['password']);

        Auth::login($user);
        return redirect()->route('dashboard')->with('ok', 'Votre compte est activé. Bienvenue !');
    }
}
