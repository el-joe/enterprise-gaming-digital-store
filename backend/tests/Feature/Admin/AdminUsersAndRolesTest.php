<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

/*
| M15: staff, roles and the permission matrix. Nobody escalates their own permissions; explicit
| grants (E) only by a Super Admin; 2FA is enforced for invited staff.
*/

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function staffWithRole(string $role): User
{
    $staff = User::factory()->staff()->withTwoFactor()->create();
    $staff->assignRole($role);

    return $staff;
}

describe('pages', function (): void {
    it('guards the staff and role pages', function (): void {
        adminWith(['orders.view']);
        $this->get('/admin/users/staff')->assertForbidden();
        $this->get('/admin/users/roles')->assertForbidden();
        postTable('staff')->assertForbidden();

        adminWith(['users.manage']);
        $page = $this->get('/admin/users/staff')->assertOk()->assertSee(__('admin.users.invite'));
        assertCspSafeHtml($page);
        $this->get('/admin/users/roles')->assertOk()->assertDontSee('id="role-', false);

        adminWith(['roles.manage']);
        $roles = $this->get('/admin/users/roles')->assertOk()->assertSee('id="role-', false)->assertSee('suppliers.credentials');
        assertCspSafeHtml($roles);
        $this->withSession(['admin.locale' => 'ar'])->get('/admin/users/roles')->assertOk()->assertSee('صلاحيات الأدوار');
    });

    it('lists staff only, with their 2FA state', function (): void {
        User::factory()->create();
        $member = staffWithRole('Support Agent');
        adminWith(['users.manage']);

        $rows = postTable('staff', ['search' => ['value' => $member->email]])->assertOk()->json('data');

        expect($rows)->toHaveCount(1)->and($rows[0]['two_factor'])->toContain(__('admin.users.on'))->and($rows[0]['roles'])->toContain('Support Agent');
        postTable('staff')->assertJsonPath('recordsTotal', 2);
    });

    it('shows a read-only own account', function (): void {
        $me = adminWith(['users.manage']);

        $this->get('/admin/users/staff/'.$me->public_id)->assertOk()->assertSee(__('admin.users.self_note'))->assertDontSee(__('admin.users.save_roles'));
    });
});

describe('invites and roles', function (): void {
    it('invites staff with a reset link, roles and audit; the invitee must set up 2FA', function (): void {
        Notification::fake();
        $admin = adminWithRole('Super Admin');

        $this->postJson('/admin/api/staff', ['name' => 'Lina', 'email' => 'lina@example.com', 'roles' => ['Support Agent']])->assertCreated();

        $invited = User::query()->where('email', 'lina@example.com')->firstOrFail();
        expect($invited->isStaff())->toBeTrue()->and($invited->hasRole('Support Agent'))->toBeTrue()
            ->and(AuditLog::query()->where('action', 'users.staff_invited')->exists())->toBeTrue();
        Notification::assertSentTo($invited, ResetPassword::class);

        $this->actingAs($invited)->get('/admin/support/tickets')->assertRedirect(route('admin.two-factor.setup'));
        expect($admin->is($invited))->toBeFalse();
    });

    it('refuses granting a role with permissions the actor does not hold', function (): void {
        $member = staffWithRole('Support Agent');
        adminWith(['users.manage', 'support.view']);

        $this->putJson("/admin/api/staff/{$member->public_id}/roles", ['roles' => ['Finance Manager']])
            ->assertForbidden()->assertJsonPath('code', 'forbidden_escalation')->assertJsonValidationErrors('permissions');

        expect($member->refresh()->hasRole('Finance Manager'))->toBeFalse();
    });

    it('refuses any change to the actor\'s own account', function (): void {
        $me = adminWithRole('Super Admin');

        $this->putJson("/admin/api/staff/{$me->public_id}/roles", ['roles' => []])->assertForbidden()->assertJsonPath('code', 'forbidden_escalation');
        $this->putJson("/admin/api/staff/{$me->public_id}/permissions", ['permissions' => ['digital_codes.reveal']])->assertForbidden();
        $this->postJson("/admin/api/staff/{$me->public_id}/reset-two-factor", ['reason' => 'Lost my phone'])->assertForbidden();
        $this->postJson("/admin/api/staff/{$me->public_id}/status", ['status' => 'suspended', 'reason' => 'Leaving now'])->assertForbidden();
    });

    it('lets only a Super Admin give the Super Admin role and explicit permissions', function (): void {
        $member = staffWithRole('Support Agent');
        $admin = User::factory()->staff()->withTwoFactor()->create();
        $admin->assignRole('Admin');
        $admin->givePermissionTo('users.manage');
        actingAsVerifiedStaff($admin);
        storefrontWithoutBuild();

        $this->putJson("/admin/api/staff/{$member->public_id}/roles", ['roles' => ['Super Admin']])->assertForbidden();
        $this->putJson("/admin/api/staff/{$member->public_id}/permissions", ['permissions' => ['digital_codes.reveal']])->assertForbidden();

        adminWithRole('Super Admin');
        $this->putJson("/admin/api/staff/{$member->public_id}/permissions", ['permissions' => ['digital_codes.reveal']])->assertOk();
        $this->putJson("/admin/api/staff/{$member->public_id}/roles", ['roles' => ['Support Agent', 'Super Admin']])->assertOk();

        expect($member->refresh()->hasDirectPermission('digital_codes.reveal'))->toBeTrue()->and($member->hasRole('Super Admin'))->toBeTrue()
            ->and(AuditLog::query()->where('action', 'users.permissions_changed')->exists())->toBeTrue();
    });

    it('suspends staff with a reason and resets their 2FA, audited', function (): void {
        $member = staffWithRole('Support Agent');
        adminWith(['users.manage']);

        $this->postJson("/admin/api/staff/{$member->public_id}/status", ['status' => 'closed', 'reason' => 'Leaving now'])->assertUnprocessable();
        $this->postJson("/admin/api/staff/{$member->public_id}/status", ['status' => 'suspended', 'reason' => 'Leaving now'])->assertOk();
        $this->postJson("/admin/api/staff/{$member->public_id}/reset-two-factor", ['reason' => 'Lost phone'])->assertOk();

        $member->refresh();
        expect($member->status)->toBe(UserStatus::Suspended)->and($member->two_factor_confirmed_at)->toBeNull()
            ->and(AuditLog::query()->where('action', 'users.two_factor_reset')->value('reason'))->toBe('Lost phone');
    });

    it('answers 404 for customers on staff routes', function (): void {
        $customer = User::factory()->create();
        adminWith(['users.manage']);

        $this->putJson("/admin/api/staff/{$customer->public_id}/roles", ['roles' => []])->assertNotFound();
        $this->get('/admin/users/staff/'.$customer->public_id)->assertNotFound();
    });
});

describe('permission matrix', function (): void {
    it('saves a role column with roles.manage and refuses E permissions, the Super Admin role and own roles', function (): void {
        $role = Role::findByName('Catalog Manager', 'web');
        $superAdmin = Role::findByName('Super Admin', 'web');
        $me = adminWithRole('Super Admin');
        $me->givePermissionTo('roles.manage');

        $this->putJson("/admin/api/roles/{$role->id}/permissions", ['permissions' => ['catalog.view', 'payments.credentials']])
            ->assertForbidden()->assertJsonValidationErrors('permissions');
        $this->putJson("/admin/api/roles/{$superAdmin->id}/permissions", ['permissions' => []])->assertForbidden();
        $this->putJson("/admin/api/roles/{$role->id}/permissions", ['permissions' => ['bogus.permission']])->assertUnprocessable();
        $this->putJson("/admin/api/roles/{$role->id}/permissions", ['permissions' => ['catalog.view', 'catalog.manage']])->assertOk();

        expect($role->refresh()->permissions->pluck('name')->sort()->values()->all())->toBe(['catalog.manage', 'catalog.view'])
            ->and(AuditLog::query()->where('action', 'roles.permissions_changed')->exists())->toBeTrue();

        $holder = staffWithRole('Catalog Manager');
        $holder->givePermissionTo('roles.manage');
        actingAsVerifiedStaff($holder);
        $this->putJson("/admin/api/roles/{$role->id}/permissions", ['permissions' => ['catalog.view', 'catalog.manage', 'catalog.publish']])->assertForbidden();
    });

    it('creates and deletes custom roles but never seeded ones', function (): void {
        adminWith(['roles.manage']);

        $this->postJson('/admin/api/roles', ['name' => 'Night shift'])->assertCreated();
        $this->postJson('/admin/api/roles', ['name' => 'Night shift'])->assertUnprocessable()->assertJsonValidationErrors('name');
        $custom = Role::findByName('Night shift', 'web');
        $this->deleteJson("/admin/api/roles/{$custom->id}")->assertOk();
        $this->deleteJson('/admin/api/roles/'.Role::findByName('Admin', 'web')->id)->assertForbidden();
    });

    it('refuses the role routes without roles.manage', function (): void {
        adminWith(['users.manage']);

        $this->postJson('/admin/api/roles', ['name' => 'Night shift'])->assertForbidden();
        $this->putJson('/admin/api/roles/'.Role::findByName('Admin', 'web')->id.'/permissions', ['permissions' => []])->assertForbidden();
    });
});
