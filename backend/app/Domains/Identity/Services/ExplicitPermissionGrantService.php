<?php

namespace App\Domains\Identity\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Enums\AuditLogActorType;
use App\Domains\Audit\Enums\SecurityEventSeverity;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Audit\Services\SecurityEventRecorder;
use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Identity\Enums\RoleName;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Enums\UserType;
use App\Domains\Identity\Models\User;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Server-side grant of explicit-grant (E) permissions (SECURITY.md section 4). In the admin panel
 * these come only from another Super Admin, and nobody grants to themselves; with a single Super
 * Admin nobody could hold them. Shell access to the server is already the root of trust (the
 * first Super Admin is created the same way), so `php artisan staff:grant-explicit` may grant or
 * revoke them, only for an active staff Super Admin, only (E) permissions, always audited as a
 * system action and recorded as a security event.
 */
class ExplicitPermissionGrantService
{
    public function __construct(
        private AuditLogger $audit,
        private SecurityEventRecorder $securityEvents,
        private PermissionRegistrar $registrar,
    ) {}

    /**
     * @return list<PermissionName>
     */
    public static function explicitPermissions(): array
    {
        return array_values(array_filter(PermissionName::cases(), static fn (PermissionName $permission): bool => $permission->requiresExplicitGrant()));
    }

    /**
     * The explicit-grant permissions the staff member holds directly.
     *
     * @return list<string>
     */
    public function heldBy(User $staff): array
    {
        $explicit = array_map(static fn (PermissionName $permission): string => $permission->value, self::explicitPermissions());

        return array_values(array_intersect($explicit, $staff->getDirectPermissions()->pluck('name')->all()));
    }

    /**
     * Grant (or revoke) explicit-grant permissions. Returns the explicit permissions held afterwards.
     *
     * @param  list<PermissionName>  $permissions
     * @return list<string>
     *
     * @throws ApplicationException
     */
    public function change(User $staff, array $permissions, bool $revoke): array
    {
        if ($staff->type !== UserType::Staff || $staff->status !== UserStatus::Active) {
            throw new ApplicationException('Only an active staff account can hold explicit-grant permissions.', 'not_active_staff', 422);
        }

        if (! $staff->hasRole(RoleName::SuperAdmin->value)) {
            throw new ApplicationException('Explicit-grant permissions are given from the server only to a Super Admin.', 'not_super_admin', 422);
        }

        foreach ($permissions as $permission) {
            if (! $permission->requiresExplicitGrant()) {
                throw new ApplicationException("{$permission->value} is not an explicit-grant permission; manage it through roles in the admin panel.", 'not_explicit_permission', 422);
            }
        }

        $names = array_values(array_unique(array_map(static fn (PermissionName $permission): string => $permission->value, $permissions)));

        return DB::transaction(function () use ($staff, $names, $revoke): array {
            $before = $this->heldBy($staff);

            $revoke ? $staff->revokePermissionTo($names) : $staff->givePermissionTo($names);
            $this->registrar->forgetCachedPermissions();
            $staff->unsetRelation('permissions');

            $after = $this->heldBy($staff->fresh());

            if ($before !== $after) {
                $this->audit->record(new AuditEntry(
                    'users.explicit_permissions_changed',
                    $staff,
                    ['permissions' => $before],
                    ['permissions' => $after],
                    'Changed from the server with php artisan staff:grant-explicit',
                    AuditLogActorType::System,
                ));

                $this->securityEvents->record('staff.explicit_permissions_changed', SecurityEventSeverity::Warning, null, $staff->id, [
                    'granted' => array_values(array_diff($after, $before)),
                    'revoked' => array_values(array_diff($before, $after)),
                    'source' => 'server_command',
                ]);
            }

            return $after;
        });
    }
}
