<?php

namespace App\Http\Controllers\Admin\Users;

use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\StaffAdminService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Customers\CustomerReasonRequest;
use App\Http\Requests\Admin\Users\InviteStaffRequest;
use App\Http\Requests\Admin\Users\SetStaffStatusRequest;
use App\Http\Requests\Admin\Users\SyncPermissionsRequest;
use App\Http\Requests\Admin\Users\SyncStaffRolesRequest;
use Illuminate\Http\JsonResponse;

/**
 * Staff accounts (M15, spec Phase 9.11; `users.manage`): invite, roles, direct permissions,
 * status and two-step reset. Escalation rules live in StaffAdminService.
 */
class StaffController extends Controller
{
    public function __construct(private StaffAdminService $staff) {}

    public function store(InviteStaffRequest $request): JsonResponse
    {
        $user = $this->staff->invite($request->user(), (string) $request->validated('name'), (string) $request->validated('email'), $request->validated('roles'));

        return $this->success(['id' => $user->public_id], 'Invitation sent. Two-step verification is required at first sign-in.', status: 201);
    }

    public function roles(SyncStaffRolesRequest $request, User $staff): JsonResponse
    {
        $user = $this->staff->syncRoles($request->user(), $staff, $request->validated('roles'));

        return $this->success(['roles' => $user->getRoleNames()->all()], 'Roles saved.');
    }

    public function permissions(SyncPermissionsRequest $request, User $staff): JsonResponse
    {
        $user = $this->staff->syncDirectPermissions($request->user(), $staff, $request->validated('permissions'));

        return $this->success(['permissions' => $user->getDirectPermissions()->pluck('name')->all()], 'Permissions saved.');
    }

    public function status(SetStaffStatusRequest $request, User $staff): JsonResponse
    {
        $this->staff->setStatus($request->user(), $staff, UserStatus::from((string) $request->validated('status')), (string) $request->validated('reason'));

        return $this->success(message: 'Status saved.');
    }

    public function resetTwoFactor(CustomerReasonRequest $request, User $staff): JsonResponse
    {
        $this->staff->resetTwoFactor($request->user(), $staff, (string) $request->validated('reason'));

        return $this->success(message: 'Two-step verification reset. They must set it up again.');
    }
}
