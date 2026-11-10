<?php

namespace App\Http\Controllers\Account;

use App\Domains\Identity\Services\ProfileService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateProfileRequest;
use App\Http\Resources\Account\ProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET and PATCH /account/profile (M-A5, gap G5). A web session localizes its next requests with
 * the saved `locale` (SetLocaleFromRequest); a stateless client keeps sending `Accept-Language`.
 */
class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return $this->success(['profile' => ProfileResource::make($request->user())->resolve()]);
    }

    public function update(UpdateProfileRequest $request, ProfileService $profiles): JsonResponse
    {
        $user = $profiles->update($request->user(), $request->toChanges());

        return $this->success(['profile' => ProfileResource::make($user)->resolve()], 'Profile updated.');
    }
}
