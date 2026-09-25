<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Membre;
use App\Services\AccountService;
use App\Support\Journal;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private AccountService $comptes) {}

    /** Libellés des rôles (source unique : catalogue Permissions). */
    public const ROLES = Permissions::ROLES;

    /**
     * Le compte super administrateur n'est modifiable que par lui-même
     * (un administrateur ordinaire ne peut ni le modifier, ni le suspendre, ni le supprimer).
     */
    private function proteger(User $user): void
    {
        abort_if($user->estSuperAdmin() && ! auth()->user()->estSuperAdmin(), 403,
            'Le compte super administrateur est protégé.');
    }

    /** Rôles proposés à la création d'un compte. */
    private function rolesAttribuables(): array
    {
        $roles = self::ROLES;
        if (! config('jci.acces_membres')) {
            unset($roles['membre']); // accès membres fermé : pas de compte « membre »
        }
        return $roles;
    }

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
        return view('admin.users.index', ['users' => $users, 'roles' => self::ROLES + [Permissions::ROLE_SUPER => 'Super administrateur']]);
    }

    public function create()
    {
        $membresSansCompte = Membre::whereDoesntHave('user')->whereNotNull('email')->orderBy('nom')->get();
        return view('admin.users.create', ['roles' => $this->rolesAttribuables(), 'membres' => $membresSansCompte]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'membre_id' => ['required', 'exists:membres,id'],
            'role'      => ['required', Rule::in(array_keys($this->rolesAttribuables()))],
        ]);

        // M1 — Président / Trésorier : uniquement via l'affectation du CDL (Mandats).
        if (in_array($data['role'], Permissions::ROLES_CDL_SENSIBLES, true)) {
            return back()->withInput()->withErrors(['role' =>
                "Les postes Président et Trésorier s'attribuent uniquement via l'affectation du CDL (menu Mandats)."]);
        }

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
        $this->proteger($user);
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
        $this->proteger($user);
        $data = $request->validate([
            'email'         => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'          => ['required', Rule::in(array_keys(self::ROLES))],
            'permissions'   => ['array'],
            'permissions.*' => [Rule::in(Permissions::slugs())],
        ]);

        $estMoi       = $user->id === auth()->id();
        $roleActuel   = $user->getRoleNames()->first();
        $compteSensible = in_array($roleActuel, Permissions::ROLES_CDL_SENSIBLES, true);

        // M1 — Changer l'email d'un autre compte permettrait de le détourner
        // (nouvel email + « mot de passe oublié »). Interdit pour les comptes
        // Président / Trésorier ; pour les autres, l'ancienne adresse est prévenue
        // et toutes les sessions du compte sont fermées.
        if (! $estMoi && $data['email'] !== $user->email) {
            if ($compteSensible) {
                return back()->withInput()->withErrors(['email' =>
                    "L'email d'un compte Président / Trésorier ne peut pas être modifié depuis l'administration."]);
            }
            $ancien = $user->email;
            $user->update(['email' => $data['email']]);
            $this->comptes->fermerSessions($user);
            $this->comptes->prevenirChangementEmail($user, $ancien);
            Journal::ecrire('EMAIL_CHANGE', User::class, $user->id, ['email' => $ancien], ['email' => $data['email']]);
        } elseif ($estMoi) {
            $user->update(['email' => $data['email']]);
        }

        // Sécurité : un administrateur ne peut pas modifier SON PROPRE rôle ni
        // ses propres permissions (protection contre l'auto-élévation / auto-blocage).
        if ($estMoi) {
            return redirect()->route('admin.users.index')
                ->with('ok', "Email mis à jour. Vous ne pouvez pas modifier votre propre rôle ni vos propres permissions.");
        }

        // M1 — Président / Trésorier s'attribuent et se retirent uniquement via le CDL (Mandats).
        $nouveauSensible = in_array($data['role'], Permissions::ROLES_CDL_SENSIBLES, true);
        if (($compteSensible || $nouveauSensible) && $data['role'] !== $roleActuel) {
            return back()->withInput()->withErrors(['role' =>
                "Les postes Président et Trésorier s'attribuent uniquement via l'affectation du CDL (menu Mandats)."]);
        }

        $avant = ['role' => $roleActuel, 'permissions' => $user->getDirectPermissions()->pluck('name')->implode(', ')];

        $user->syncRoles([$data['role']]);
        $this->comptes->desactiverAnciensDuRole($data['role'], $user->id);

        // Permissions supplémentaires (directes), en plus de celles du rôle.
        // Les permissions financières ne sont JAMAIS attribuables en direct.
        $perms = Permissions::filtrerFinances($data['permissions'] ?? []);
        $user->syncPermissions($perms);

        Journal::ecrire('USER_PRIVILEGES', User::class, $user->id, $avant,
            ['role' => $data['role'], 'permissions' => implode(', ', $perms)]);

        return redirect()->route('admin.users.index')->with('ok', "Privilèges de {$user->name} mis à jour.");
    }

    public function renvoyerActivation(User $user)
    {
        $this->proteger($user);
        $envoye = $this->comptes->envoyerActivation($user);
        return back()->with('ok', $envoye
            ? "Lien d'activation renvoyé à {$user->email}."
            : "Échec de l'envoi (vérifier la configuration SMTP).");
    }

    public function resetPassword(User $user)
    {
        $this->proteger($user);
        \Illuminate\Support\Facades\Password::sendResetLink(['email' => $user->email]);
        return back()->with('ok', "Lien de réinitialisation envoyé à {$user->email}.");
    }

    /** Suspendre / réactiver un compte (confirmation demandée côté interface). */
    public function toggleActif(User $user)
    {
        $this->proteger($user);
        if ($user->id === auth()->id()) {
            return back()->with('ok', "Vous ne pouvez pas suspendre votre propre compte.");
        }
        $user->update(['actif' => ! $user->actif]);
        if (! $user->actif) {
            $this->comptes->fermerSessions($user); // coupe les sessions et le « se souvenir de moi »
        }
        return back()->with('ok', $user->actif ? 'Compte réactivé.' : 'Compte suspendu.');
    }

    public function destroy(User $user)
    {
        $this->proteger($user);
        if ($user->id === auth()->id()) {
            return back()->with('ok', "Vous ne pouvez pas supprimer votre propre compte.");
        }
        $user->delete();
        return back()->with('ok', 'Compte supprimé.');
    }
}
