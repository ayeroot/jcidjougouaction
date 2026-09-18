<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Membre;
use App\Services\AccountService;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private AccountService $comptes) {}

    /** Libellés des rôles (source unique : catalogue Permissions). */
    public const ROLES = Permissions::ROLES;

    /** Liste avec recherche et filtres (rôle, état). */
    public function index(Request $request)
    {
        $query = User::with('roles', 'membre');

        if ($request->filled('recherche')) {
            $r = $request->recherche;
            $query->where(fn ($q) => $q->where('name', 'like', "%$r%")->orWhere('email', 'like', "%$r%"));
        }
        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
        }
        if ($request->filled('etat')) {
            if ($request->etat === 'actif')     $query->where('actif', true)->whereNull('activation_token');
            if ($request->etat === 'suspendu')  $query->where('actif', false);
            if ($request->etat === 'attente')   $query->whereNotNull('activation_token');
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        return view('admin.users.index', ['users' => $users, 'roles' => self::ROLES]);
    }

    public function create()
    {
        $membresSansCompte = Membre::whereDoesntHave('user')->whereNotNull('email')->orderBy('nom')->get();
        return view('admin.users.create', ['roles' => self::ROLES, 'membres' => $membresSansCompte]);
    }

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

    /** Fiche détaillée : rôle, permissions du rôle, permissions directes. */
    public function edit(User $user)
    {
        $user->load('roles', 'permissions', 'membre');
        $role = $user->getRoleNames()->first();
        $permsDuRole = $role ? Role::findByName($role)->permissions->pluck('name')->all() : [];

        return view('admin.users.edit', [
            'user'         => $user,
            'roles'        => self::ROLES,
            'catalogue'    => Permissions::parGroupe(),
            'permsDuRole'  => $permsDuRole,
            'permsDirectes'=> $user->getDirectPermissions()->pluck('name')->all(),
            'estMoi'       => $user->id === auth()->id(),
        ]);
    }

    /**
     * Met à jour l'email (admin uniquement), le rôle et les permissions
     * supplémentaires (directes). Protège contre l'auto-élévation.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'email'         => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'          => ['required', Rule::in(array_keys(self::ROLES))],
            'permissions'   => ['array'],
            'permissions.*' => [Rule::in(Permissions::slugs())],
        ]);

        $user->update(['email' => $data['email']]);

        // Sécurité : un administrateur ne peut pas modifier SON PROPRE rôle ni
        // ses propres permissions (protection contre l'auto-élévation / auto-blocage).
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('ok', "Email mis à jour. Vous ne pouvez pas modifier votre propre rôle ni vos propres permissions.");
        }

        $user->syncRoles([$data['role']]);
        $this->comptes->desactiverAnciensDuRole($data['role'], $user->id);

        // Permissions supplémentaires (directes), en plus de celles du rôle.
        $user->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.users.index')->with('ok', "Privilèges de {$user->name} mis à jour.");
    }

    public function renvoyerActivation(User $user)
    {
        $envoye = $this->comptes->envoyerActivation($user);
        return back()->with('ok', $envoye
            ? "Lien d'activation renvoyé à {$user->email}."
            : "Échec de l'envoi (vérifier la configuration SMTP).");
    }

    public function resetPassword(User $user)
    {
        \Illuminate\Support\Facades\Password::sendResetLink(['email' => $user->email]);
        return back()->with('ok', "Lien de réinitialisation envoyé à {$user->email}.");
    }

    /** Suspendre / réactiver un compte (confirmation demandée côté interface). */
    public function toggleActif(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('ok', "Vous ne pouvez pas suspendre votre propre compte.");
        }
        $user->update(['actif' => ! $user->actif]);
        return back()->with('ok', $user->actif ? 'Compte réactivé.' : 'Compte suspendu.');
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
