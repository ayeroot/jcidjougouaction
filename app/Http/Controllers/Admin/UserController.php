<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Membre;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(private AccountService $comptes) {}

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
        // Seuls les membres n'ayant pas encore de compte peuvent être sélectionnés.
        $membresSansCompte = Membre::whereDoesntHave('user')->whereNotNull('email')->orderBy('nom')->get();
        return view('admin.users.create', [
            'roles'   => self::ROLES,
            'membres' => $membresSansCompte,
        ]);
    }

    /**
     * L'admin sélectionne un membre existant et lui attribue un rôle.
     * Les informations personnelles proviennent de la fiche membre (aucune ressaisie).
     * Un email d'activation est envoyé au membre pour qu'il définisse son mot de passe.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'membre_id' => ['required', 'exists:membres,id'],
            'role'      => ['required', Rule::in(array_keys(self::ROLES))],
        ]);

        $membre = Membre::findOrFail($data['membre_id']);
        $res = $this->comptes->creerPourMembre($membre, $data['role']);

        if (! $res['user']) {
            return back()->withInput()->with('ok', $res['message']);
        }

        $msg = $res['email_envoye']
            ? "Compte créé pour {$membre->nom_complet}. Un email d'activation a été envoyé à {$membre->email}."
            : "Compte créé, mais l'email d'activation n'a pas pu être envoyé (vérifier la configuration SMTP).";

        return redirect()->route('admin.users.index')->with('ok', $msg);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user'  => $user->load('roles', 'membre'),
            'roles' => self::ROLES,
        ]);
    }

    /**
     * L'administrateur peut modifier le rôle et — contrairement à l'utilisateur — l'email.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'  => ['required', Rule::in(array_keys(self::ROLES))],
        ]);

        $user->update(['email' => $data['email']]);
        $user->syncRoles([$data['role']]);
        $this->comptes->desactiverAnciensDuRole($data['role'], $user->id);

        return redirect()->route('admin.users.index')->with('ok', 'Compte mis à jour.');
    }

    /** Renvoie un email d'activation (compte non encore activé). */
    public function renvoyerActivation(User $user)
    {
        $envoye = $this->comptes->envoyerActivation($user);
        return back()->with('ok', $envoye
            ? "Lien d'activation renvoyé à {$user->email}."
            : "Échec de l'envoi (vérifier la configuration SMTP).");
    }

    /** Envoie un lien de réinitialisation de mot de passe. */
    public function resetPassword(User $user)
    {
        \Illuminate\Support\Facades\Password::sendResetLink(['email' => $user->email]);
        return back()->with('ok', "Lien de réinitialisation envoyé à {$user->email}.");
    }

    /** Active / désactive un compte (la désactivation bloque la connexion). */
    public function toggleActif(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('ok', "Vous ne pouvez pas désactiver votre propre compte.");
        }
        $user->update(['actif' => ! $user->actif]);
        return back()->with('ok', $user->actif ? 'Compte activé.' : 'Compte désactivé.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('ok', "Vous ne pouvez pas supprimer votre propre compte.");
        }
        $user->delete();
        return back()->with('ok', 'Compte supprimé.');
    }
}
