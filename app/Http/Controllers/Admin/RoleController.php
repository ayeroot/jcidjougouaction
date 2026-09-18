<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        $role->syncPermissions($perms);
        return back()->with('ok', "Permissions du rôle « ".(Permissions::ROLES[$role->name] ?? $role->name)." » mises à jour.");
    }

    /** Réinitialise un rôle à ses permissions par défaut (définies dans le code). */
    public function reset(Role $role)
    {
        $role->syncPermissions(Permissions::DEFAUTS_ROLES[$role->name] ?? []);
        return back()->with('ok', "Rôle « ".(Permissions::ROLES[$role->name] ?? $role->name)." » réinitialisé aux valeurs par défaut.");
    }
}
