<?php

namespace Database\Seeders;

use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Catalogue des permissions + rôles avec leurs droits par défaut.
 * Sans données personnelles : utilisable en production (appelé par jci:installer).
 * Idempotent : peut être relancé sans risque.
 */
class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::slugs() as $slug) {
            Permission::firstOrCreate(['name' => $slug, 'guard_name' => 'web']);
        }

        foreach (array_keys(Permissions::DEFAUTS_ROLES) as $nom) {
            $role = Role::firstOrCreate(['name' => $nom, 'guard_name' => 'web']);
            // On ne réécrit les droits que d'un rôle tout neuf : les réglages faits
            // depuis « Rôles & permissions » sont conservés.
            if ($role->wasRecentlyCreated) {
                $role->syncPermissions(Permissions::DEFAUTS_ROLES[$nom]);
            }
        }

        // Super administrateur : aucun droit stocké, il les a tous via Gate::before.
        Role::firstOrCreate(['name' => Permissions::ROLE_SUPER, 'guard_name' => 'web']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
