<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Account\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * Identify the signed-in staff member and the permissions used to build the admin navigation.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('roles.permissions', 'permissions');

        return $this->success([
            'user' => UserResource::make($user)->resolve(),
            'roles' => $user->roles->pluck('name')->all(),
            'permissions' => $user->getAllPermissions()->pluck('name')->sort()->values()->all(),
        ]);
    }
}
