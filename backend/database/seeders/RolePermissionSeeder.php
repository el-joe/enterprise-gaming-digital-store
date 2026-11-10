<?php

namespace Database\Seeders;

use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Identity\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the permissions and role grants from SECURITY.md section 4.
     */
    public function run(): void
    {
        foreach (PermissionName::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
        }

        foreach (RoleName::cases() as $roleName) {
            Role::findOrCreate($roleName->value, 'web')->syncPermissions(
                array_map(static fn (PermissionName $permission): string => $permission->value, $roleName->permissions()),
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
