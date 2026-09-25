<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Journal;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /** Rôles dont les permissions ne doivent pas être vidées par erreur. */
    private const PROTEGES = ['admin'];

    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        return view('admin.roles.index', [
            'roles'     => $roles,
            'labels'    => Permissions::ROLES,
            'catalogue' => Permissions::parGroupe(),
        ]);
    }

    /** Met à jour les permissions d'un rôle (impacte tous ses utilisateurs). */
    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'permissions'   => ['array'],
            'permissions.*' => [Rule::in(Permissions::slugs())],
        ]);

        // Garde-fou : le rôle admin conserve toujours la gestion des comptes.
        $perms = $data['permissions'] ?? [];
        if ($role->name === 'admin' && ! in_array('utilisateurs.gerer', $perms, true)) {
            $perms[] = 'utilisateurs.gerer';
        }

        // Séparation des pouvoirs (M1) : les permissions financières restent
        // réservées au Président et au Trésorier — l'admin ne peut pas se les octroyer.
        $refusees = array_values(array_diff($perms, Permissions::filtrerFinances($perms, $role->name)));
        $perms = Permissions::filtrerFinances($perms, $role->name);

        $avant = $role->permissions->pluck('name')->sort()->values()->all();
        $role->syncPermissions($perms);
        Journal::ecrire('ROLE_PERMISSIONS', 'Spatie\\Permission\\Models\\Role', $role->id,
            ['permissions' => implode(', ', $avant)], ['permissions' => implode(', ', collect($perms)->sort()->all())]);

        $msg = "Permissions du rôle « ".(Permissions::ROLES[$role->name] ?? $role->name)." » mises à jour.";
        if ($refusees) {
            $msg .= " Ignorées (réservées au Président / Trésorier) : ".implode(', ', $refusees).'.';
        }
        return back()->with('ok', $msg);
    }

    /** Réinitialise un rôle à ses permissions par défaut (définies dans le code). */
    public function reset(Role $role)
    {
        $role->syncPermissions(Permissions::DEFAUTS_ROLES[$role->name] ?? []);
        Journal::ecrire('ROLE_RESET', 'Spatie\\Permission\\Models\\Role', $role->id);
        return back()->with('ok', "Rôle « ".(Permissions::ROLES[$role->name] ?? $role->name)." » réinitialisé aux valeurs par défaut.");
    }
}
