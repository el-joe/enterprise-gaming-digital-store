<?php

use App\Domains\Audit\Enums\AuditLogActorType;
use App\Domains\Audit\Models\AuditLog;
use App\Domains\Audit\Models\SecurityEvent;
use App\Domains\Identity\Enums\RoleName;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\User;
use Database\Seeders\RolePermissionSeeder;

/*
| staff:grant-explicit: explicit-grant (E) permissions for a Super Admin from the server, because a
| single Super Admin can never receive them in the admin panel (SECURITY.md section 4).
*/

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->superAdmin = User::factory()->staff()->create(['email' => 'owner@example.com']);
    $this->superAdmin->assignRole(RoleName::SuperAdmin->value);
});

it('lists the explicit-grant permissions and which the Super Admin holds', function (): void {
    $this->artisan('staff:grant-explicit', ['email' => 'owner@example.com'])
        ->expectsTable(['Explicit-grant permission', 'Held'], [
            ['suppliers.credentials', 'no'],
            ['digital_codes.reveal', 'no'],
            ['payments.credentials', 'no'],
            ['settings.manage_critical', 'no'],
            ['service_accounts.manage', 'no'],
        ])
        ->assertSuccessful();
});

it('grants chosen permissions, audits it as a system action and records a security event', function (): void {
    $this->artisan('staff:grant-explicit', ['email' => 'OWNER@example.com', 'permissions' => ['payments.credentials', 'suppliers.credentials']])
        ->expectsOutputToContain('Granted')
        ->assertSuccessful();

    $owner = $this->superAdmin->fresh();
    expect($owner->hasPermissionTo('payments.credentials'))->toBeTrue()
        ->and($owner->hasPermissionTo('suppliers.credentials'))->toBeTrue()
        ->and($owner->hasPermissionTo('digital_codes.reveal'))->toBeFalse();

    $audit = AuditLog::query()->where('action', 'users.explicit_permissions_changed')->sole();
    expect($audit->actor_type)->toBe(AuditLogActorType::System)
        ->and($audit->before)->toBe(['permissions' => []])
        ->and($audit->after['permissions'])->toEqualCanonicalizing(['payments.credentials', 'suppliers.credentials']);

    expect(SecurityEvent::query()->where('type', 'staff.explicit_permissions_changed')->count())->toBe(1);
});

it('grants all explicit-grant permissions with "all", then revokes them', function (): void {
    $this->artisan('staff:grant-explicit', ['email' => 'owner@example.com', 'permissions' => ['all']])->assertSuccessful();

    expect($this->superAdmin->fresh()->getDirectPermissions()->pluck('name')->all())->toEqualCanonicalizing([
        'suppliers.credentials', 'digital_codes.reveal', 'payments.credentials', 'settings.manage_critical', 'service_accounts.manage',
    ]);

    $this->artisan('staff:grant-explicit', ['email' => 'owner@example.com', 'permissions' => ['payments.credentials'], '--revoke' => true])
        ->expectsOutputToContain('Revoked')
        ->assertSuccessful();

    expect($this->superAdmin->fresh()->hasPermissionTo('payments.credentials'))->toBeFalse();
});

it('does not audit a grant that changes nothing', function (): void {
    $this->artisan('staff:grant-explicit', ['email' => 'owner@example.com', 'permissions' => ['payments.credentials']])->assertSuccessful();
    $this->artisan('staff:grant-explicit', ['email' => 'owner@example.com', 'permissions' => ['payments.credentials']])->assertSuccessful();

    expect(AuditLog::query()->where('action', 'users.explicit_permissions_changed')->count())->toBe(1);
});

it('refuses anyone who is not an active staff Super Admin', function (string $who): void {
    $user = match ($who) {
        'customer' => User::factory()->create(['email' => 'target@example.com']),
        'staff without Super Admin' => tap(User::factory()->staff()->create(['email' => 'target@example.com']))->assignRole(RoleName::Admin->value),
        'suspended Super Admin' => tap(User::factory()->staff()->create(['email' => 'target@example.com', 'status' => UserStatus::Suspended]))->assignRole(RoleName::SuperAdmin->value),
    };

    $this->artisan('staff:grant-explicit', ['email' => 'target@example.com', 'permissions' => ['payments.credentials']])->assertFailed();

    expect($user->fresh()->getDirectPermissions())->toBeEmpty()
        ->and(AuditLog::query()->where('action', 'users.explicit_permissions_changed')->exists())->toBeFalse();
})->with(['customer', 'staff without Super Admin', 'suspended Super Admin']);

it('grants only explicit-grant permissions', function (string $permission): void {
    $this->artisan('staff:grant-explicit', ['email' => 'owner@example.com', 'permissions' => [$permission]])->assertFailed();

    expect($this->superAdmin->fresh()->getDirectPermissions())->toBeEmpty();
})->with(['an ordinary permission' => ['orders.view'], 'an unknown permission' => ['payments.everything']]);

it('fails for an unknown email', function (): void {
    $this->artisan('staff:grant-explicit', ['email' => 'nobody@example.com', 'permissions' => ['all']])
        ->expectsOutputToContain('No account has that email.')
        ->assertFailed();
});
