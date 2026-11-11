<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\StaffAdminService;
use App\Http\Controllers\Controller;
use App\Http\Support\AdminTablePage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Users and permissions (M15, spec Phase 9.11, Figma "Admin Users and Permissions" 21:5771):
 * staff list with invite, a staff page (roles, direct permissions, status, two-step reset) and
 * the role permission matrix. Controls the viewer may not use (their own account, roles they
 * hold, the Super Admin role, explicit-grant permissions) are shown disabled; the API refuses
 * them anyway.
 */
class UsersPageController extends Controller
{
    public function __construct(private StaffAdminService $staff) {}

    public function index(Request $request, AdminTablePage $tables): View
    {
        return view('admin.users.staff', [
            'table' => $tables->make('staff', $request->user()),
            'roles' => $this->staff->roles(),
        ]);
    }

    public function show(Request $request, User $staff): Response
    {
        if (! $staff->isStaff()) {
            throw new NotFoundHttpException;
        }

        $staff->load(['roles:id,name', 'permissions:id,name']);

        return response()->view('admin.users.show', [
            'member' => $staff,
            'roles' => $this->staff->roles(),
            'explicit' => array_values(array_filter(PermissionName::cases(), static fn (PermissionName $permission): bool => $permission->requiresExplicitGrant())),
            'isSelf' => $request->user()->is($staff),
            'isSuperAdmin' => $this->staff->isSuperAdmin($request->user()),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function roles(Request $request): View
    {
        return view('admin.users.roles', [
            'roles' => $this->staff->roles(),
            'permissions' => PermissionName::cases(),
            'isSuperAdmin' => $this->staff->isSuperAdmin($request->user()),
        ]);
    }
}
