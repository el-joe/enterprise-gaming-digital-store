<?php

use App\Domains\Identity\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Testing\TestResponse;

/**
 * M14 admin panel fixtures. Pages render without a Vite build (`storefrontWithoutBuild`).
 */
const ADMIN_CREDENTIAL_CANARY = 'sk_test_CANARY_m14_never_rendered_7f3a';

/**
 * A verified staff member holding exactly the given permissions, signed in, with pages
 * rendering without a build.
 *
 * @param  list<string>  $permissions
 */
function adminWith(array $permissions = []): User
{
    storefrontWithoutBuild();

    return actingAsStaffWith($permissions);
}

/**
 * A verified staff member holding one of the seeded roles (SECURITY.md section 4).
 */
function adminWithRole(string $role): User
{
    storefrontWithoutBuild();
    test()->seed(RolePermissionSeeder::class);

    $staff = User::factory()->staff()->withTwoFactor()->create();
    $staff->assignRole($role);

    return actingAsVerifiedStaff($staff);
}

/**
 * A DataTables server-side request body for an admin table.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function tableRequest(array $overrides = []): array
{
    return [
        'draw' => 1,
        'start' => 0,
        'length' => 25,
        'search' => ['value' => ''],
        'order' => [],
        'filters' => [],
        ...$overrides,
    ];
}

function postTable(string $key, array $overrides = []): TestResponse
{
    return test()->postJson("/admin/api/tables/{$key}", tableRequest($overrides));
}
