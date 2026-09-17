<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NouveauCompte;
use App\Models\User;
use App\Models\Membre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /** Libellés lisibles des rôles (l'administrateur choisit dans cette liste). */
    public const ROLES = [
        'admin'      => 'Administrateur',
        'president'  => 'Président Local',
        'vpe'        => 'VP Exécutive',
        'vpre'       => 'VP Relations Extérieures',
        'vpf'        => 'VP Formations',
        'vpm'        => 'VP Management',
        'vpcd'       => 'VP Croissance & Développement',
        'vp_projet'  => 'VP Projet & Thème',
        'tresorier'  => 'Trésorier Général',
        'secretaire' => 'Secrétaire Général',
        'membre'     => 'Membre',
    ];

    public function index()
    {
        $users = User::with('roles', 'membre')->orderBy('name')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create', [
            'roles'   => self::ROLES,
            'membres' => Membre::orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'      => ['required', Rule::in(array_keys(self::ROLES))],
            'membre_id' => ['nullable', 'exists:membres,id'],
        ]);

        // L'administrateur crée l'identifiant ; un mot de passe provisoire est généré.
        $motDePasse = Str::password(10);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($motDePasse),
            'membre_id' => $data['membre_id'] ?? null,
        ]);
        $user->syncRoles([$data['role']]);

        // Chaque nouvel utilisateur reçoit un email avec ses identifiants.
        $envoye = $this->envoyerEmail($user, $motDePasse, self::ROLES[$data['role']]);

        $msg = $envoye
            ? "Compte créé. Un email avec les identifiants a été envoyé à {$user->email}."
            : "Compte créé, mais l'email n'a pas pu être envoyé. Mot de passe provisoire : {$motDePasse}";

        return redirect()->route('admin.users.index')->with('ok', $msg);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user'    => $user->load('roles'),
            'roles'   => self::ROLES,
            'membres' => Membre::orderBy('nom')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'      => ['required', Rule::in(array_keys(self::ROLES))],
            'membre_id' => ['nullable', 'exists:membres,id'],
        ]);

        $user->update([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'membre_id' => $data['membre_id'] ?? null,
        ]);
        $user->syncRoles([$data['role']]);

        return redirect()->route('admin.users.index')->with('ok', 'Compte mis à jour.');
    }

    /** Réinitialise le mot de passe et renvoie un email. */
    public function resetPassword(User $user)
    {
        $motDePasse = Str::password(10);
        $user->update(['password' => Hash::make($motDePasse)]);
        $role = $user->getRoleNames()->first();
        $envoye = $this->envoyerEmail($user, $motDePasse, self::ROLES[$role] ?? 'Membre');

        return back()->with('ok', $envoye
            ? "Nouveau mot de passe envoyé à {$user->email}."
            : "Mot de passe réinitialisé : {$motDePasse} (email non envoyé).");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('ok', "Vous ne pouvez pas supprimer votre propre compte.");
        }
        $user->delete();
        return back()->with('ok', 'Compte supprimé.');
    }

    /** Envoi de l'email ; renvoie false si l'envoi échoue (SMTP non configuré, etc.). */
    private function envoyerEmail(User $user, string $motDePasse, string $roleLibelle): bool
    {
        try {
            Mail::to($user->email)->send(new NouveauCompte($user, $motDePasse, $roleLibelle));
            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }
}
