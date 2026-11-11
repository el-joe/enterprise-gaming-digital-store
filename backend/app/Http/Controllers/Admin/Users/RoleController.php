<?php

namespace App\Http\Controllers\Admin\Users;

use App\Domains\Identity\Services\StaffAdminService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\CreateRoleRequest;
use App\Http\Requests\Admin\Users\SyncPermissionsRequest;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

/**
 * Roles and the permission matrix (M15, spec Phase 9.11; `roles.manage`).
 */
class RoleController extends Controller
{
    public function __construct(private StaffAdminService $staff) {}

    public function store(CreateRoleRequest $request): JsonResponse
    {
        $role = $this->staff->createRole($request->user(), (string) $request->validated('name'));

        return $this->success(['id' => $role->id, 'name' => $role->name], 'Role created.', status: 201);
    }

    public function permissions(SyncPermissionsRequest $request, Role $role): JsonResponse
    {
        $role = $this->staff->syncRolePermissions($request->user(), $role, $request->validated('permissions'));

        return $this->success(['permissions' => $role->permissions->pluck('name')->all()], 'Permissions saved.');
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->staff->deleteRole(request()->user(), $role);

        return $this->success(message: 'Role deleted.');
    }
}
