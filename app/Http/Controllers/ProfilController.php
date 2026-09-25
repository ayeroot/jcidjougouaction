<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ProfilController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user()->load('membre');
        return view('profil.edit', compact('user'));
    }

    /**
     * L'utilisateur met à jour ses informations.
     * IMPORTANT : l'email n'est PAS modifiable ici (contrôle backend), seul l'admin le peut.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'ville'     => ['nullable', 'string', 'max:255'],
            'adresse'   => ['nullable', 'string', 'max:255'],
            'photo'     => ['nullable', 'image', 'max:2048'],
        ]);

        // On ignore tout champ 'email' éventuellement injecté : l'email reste inchangé.
        $user->update(['name' => $data['name']]);

        if ($membre = $user->membre) {
            $maj = [
                'telephone' => $data['telephone'] ?? $membre->telephone,
                'ville'     => $data['ville'] ?? $membre->ville,
                'adresse'   => $data['adresse'] ?? $membre->adresse,
            ];
            if ($request->hasFile('photo')) {
                if ($membre->photo) Storage::disk('public')->delete($membre->photo);
                $maj['photo'] = $request->file('photo')->store('membres', 'public');
            }
            $membre->update($maj);
        }

        return back()->with('ok', 'Vos informations ont été mises à jour.');
    }

    /** Changement de mot de passe par l'utilisateur lui-même. */
    public function motDePasse(Request $request)
    {
        $data = $request->validate([
            'actuel'   => ['required'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        if (! Hash::check($data['actuel'], $request->user()->password)) {
            return back()->withErrors(['actuel' => 'Mot de passe actuel incorrect.']);
        }

        $request->user()->update(['password' => Hash::make($data['password'])]);

        // Déconnecte les autres appareils (sessions + « se souvenir de moi »), garde celle-ci.
        app(\App\Services\AccountService::class)->fermerSessions($request->user(), $request->session()->getId());
        return back()->with('ok', 'Mot de passe modifié.');
    }
}
