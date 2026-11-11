<?php

namespace App\Domains\Identity\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Identity\Enums\RoleName;
use App\Domains\Identity\Enums\TokenRevocationReason;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Enums\UserType;
use App\Domains\Identity\Models\User;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Staff accounts, roles and the permission matrix (M15, spec Phase 9.11, SECURITY.md section 4).
 *
 * No escalation:
 * - nobody changes their own roles, permissions, status or two-step verification;
 * - a grant (a role for a staff member, a permission for a role or a staff member) may only
 *   hold permissions the actor holds, unless the actor is a Super Admin;
 * - explicit-grant permissions (E) are never part of a role and are granted to a staff member
 *   only by a Super Admin;
 * - the Super Admin role is fixed by the seeder and only a Super Admin assigns it.
 *
 * Invited staff get a random password and a reset link; two-step verification is enforced at
 * their first admin page. Every change is audited and clears the permission cache.
 */
class StaffAdminService
{
    public function __construct(
        private AuditLogger $audit,
        private PasswordResetService $passwords,
        private TwoFactorService $twoFactor,
        private AccountSessionService $sessions,
        private PermissionRegistrar $registrar,
    ) {}

    /**
     * @param  list<string>  $roles
     */
    public function invite(User $actor, string $name, string $email, array $roles): User
    {
        $this->ensureCanGrantRoles($actor, $roles);

        $staff = DB::transaction(function () use ($name, $email, $roles): User {
            $staff = new User(['name' => $name, 'email' => $email, 'password' => Str::random(64)]);
            $staff->forceFill(['type' => UserType::Staff, 'status' => UserStatus::Active])->save();
            $staff->syncRoles($roles);

            $this->audit->record(new AuditEntry('users.staff_invited', $staff, after: ['roles' => $roles]));

            return $staff;
        });

        $this->passwords->sendResetLink($email);
        $this->registrar->forgetCachedPermissions();

        return $staff;
    }

    /**
     * @param  list<string>  $roles
     */
    public function syncRoles(User $actor, User $staff, array $roles): User
    {
        $this->ensureOther($actor, $staff);
        $this->ensureCanGrantRoles($actor, $roles);

        $current = $staff->getRoleNames()->all();
        $removedSuperAdmin = in_array(RoleName::SuperAdmin->value, $current, true) && ! in_array(RoleName::SuperAdmin->value, $roles, true);

        if ($removedSuperAdmin && ! $this->isSuperAdmin($actor)) {
            throw $this->refused(__('admin.users.err_super_admin'));
        }

        DB::transaction(function () use ($staff, $roles, $current): void {
            $staff->syncRoles($roles);
            $this->audit->record(new AuditEntry('users.roles_changed', $staff, ['roles' => $current], ['roles' => $roles]));
        });

        $this->registrar->forgetCachedPermissions();

        return $staff->refresh();
    }

    /**
     * Direct permissions of a staff member, on top of their roles (the way to grant (E)).
     *
     * @param  list<string>  $permissions
     */
    public function syncDirectPermissions(User $actor, User $staff, array $permissions): User
    {
        $this->ensureOther($actor, $staff);
        $this->ensureCanGrantPermissions($actor, $permissions, explicitAllowed: true);

        $current = $staff->getDirectPermissions()->pluck('name')->all();

        DB::transaction(function () use ($staff, $permissions, $current): void {
            $staff->syncPermissions($permissions);
            $this->audit->record(new AuditEntry('users.permissions_changed', $staff, ['permissions' => $current], ['permissions' => $permissions]));
        });

        $this->registrar->forgetCachedPermissions();

        return $staff->refresh();
    }

    public function setStatus(User $actor, User $staff, UserStatus $status, string $reason): User
    {
        $this->ensureOther($actor, $staff);

        if ($status === UserStatus::Closed) {
            throw $this->refused(__('admin.users.err_status'));
        }

        DB::transaction(function () use ($staff, $status, $reason): void {
            $before = $staff->status?->value;
            $staff->forceFill(['status' => $status])->save();
            $this->audit->record(new AuditEntry('users.status_changed', $staff, ['status' => $before], ['status' => $status->value], $reason));
        });

        if ($status === UserStatus::Suspended) {
            $this->sessions->signOutEverywhere($staff, TokenRevocationReason::AccountInactive);
        }

        return $staff->refresh();
    }

    /**
     * Remove a staff member's two-step verification (lost phone): they are signed out and must
     * set it up again before the next admin page.
     */
    public function resetTwoFactor(User $actor, User $staff, string $reason): void
    {
        $this->ensureOther($actor, $staff);
        $this->twoFactor->disable($staff);
        $this->sessions->signOutEverywhere($staff, TokenRevocationReason::AccountInactive);
        $this->audit->record(new AuditEntry('users.two_factor_reset', $staff, reason: $reason));
    }

    public function createRole(User $actor, string $name): Role
    {
        $role = DB::transaction(function () use ($name): Role {
            $role = Role::create(['name' => $name, 'guard_name' => 'web']);
            $this->audit->record(new AuditEntry('roles.created', $role, after: ['name' => $name]));

            return $role;
        });

        $this->registrar->forgetCachedPermissions();

        return $role;
    }

    /**
     * Replace the permissions of a role (the matrix column). A role the actor holds, the Super
     * Admin role and explicit-grant permissions are refused.
     *
     * @param  list<string>  $permissions
     */
    public function syncRolePermissions(User $actor, Role $role, array $permissions): Role
    {
        if ($role->name === RoleName::SuperAdmin->value) {
            throw $this->refused(__('admin.users.err_super_admin_role'));
        }

        if ($actor->hasRole($role->name)) {
            throw $this->refused(__('admin.users.err_own_role'));
        }

        $this->ensureCanGrantPermissions($actor, $permissions, explicitAllowed: false);

        $current = $role->permissions->pluck('name')->all();

        DB::transaction(function () use ($role, $permissions, $current): void {
            $role->syncPermissions($permissions);
            $this->audit->record(new AuditEntry('roles.permissions_changed', $role, ['permissions' => $current], ['permissions' => $permissions]));
        });

        $this->registrar->forgetCachedPermissions();

        return $role->refresh();
    }

    public function deleteRole(User $actor, Role $role): void
    {
        if (in_array($role->name, array_map(static fn (RoleName $name): string => $name->value, RoleName::cases()), true)) {
            throw $this->refused(__('admin.users.err_seeded_role'));
        }

        if ($role->users()->exists()) {
            throw new ApplicationException(__('admin.users.err_role_in_use'), 'conflict', 409);
        }

        DB::transaction(function () use ($role): void {
            $this->audit->record(new AuditEntry('roles.deleted', $role, before: ['name' => $role->name, 'permissions' => $role->permissions->pluck('name')->all()]));
            $role->delete();
        });

        $this->registrar->forgetCachedPermissions();
    }

    /**
     * Every role with its permissions, for the matrix.
     *
     * @return Collection<int, Role>
     */
    public function roles(): Collection
    {
        return Role::query()->where('guard_name', 'web')->with('permissions:id,name')->withCount('users')->orderBy('id')->get();
    }

    /**
     * @return list<string>
     */
    public function permissionNames(): array
    {
        return array_map(static fn (PermissionName $permission): string => $permission->value, PermissionName::cases());
    }

    public function isSuperAdmin(User $user): bool
    {
        return $user->hasRole(RoleName::SuperAdmin->value);
    }

    /**
     * @param  list<string>  $roles
     */
    private function ensureCanGrantRoles(User $actor, array $roles): void
    {
        if ($this->isSuperAdmin($actor)) {
            return;
        }

        if (in_array(RoleName::SuperAdmin->value, $roles, true)) {
            throw $this->refused(__('admin.users.err_super_admin'));
        }

        $granted = Role::query()->whereIn('name', $roles)->with('permissions:id,name')->get()
            ->flatMap(static fn (Role $role): array => $role->permissions->pluck('name')->all())->unique()->values()->all();

        $this->ensureCanGrantPermissions($actor, $granted, explicitAllowed: false);
    }

    /**
     * @param  list<string>  $permissions
     */
    private function ensureCanGrantPermissions(User $actor, array $permissions, bool $explicitAllowed): void
    {
        $superAdmin = $this->isSuperAdmin($actor);

        foreach ($permissions as $permission) {
            $explicit = PermissionName::tryFrom($permission)?->requiresExplicitGrant() ?? false;

            if ($explicit && (! $explicitAllowed || ! $superAdmin)) {
                throw $this->refused(__('admin.users.err_explicit', ['permission' => $permission]), 'permissions');
            }

            if (! $superAdmin && ! $actor->hasPermissionTo(Permission::findByName($permission, 'web'))) {
                throw $this->refused(__('admin.users.err_escalation', ['permission' => $permission]), 'permissions');
            }
        }
    }

    private function ensureOther(User $actor, User $staff): void
    {
        if (! $staff->isStaff()) {
            throw new ApplicationException('Not found.', 'not_found', 404);
        }

        if ($actor->is($staff)) {
            throw $this->refused(__('admin.users.err_self'));
        }
    }

    private function refused(string $message, string $field = 'roles'): ApplicationException
    {
        return new ApplicationException($message, 'forbidden_escalation', 403, [$field => [$message]]);
    }
}
