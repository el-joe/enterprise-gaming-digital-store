<?php

namespace App\Http\Controllers\Account;

use App\Domains\Identity\Services\AccountSessionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ChangePasswordRequest;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * PUT /account/password (M-A5, gap G5). Only the device that changed the password stays signed
 * in (AccountSessionService::changePassword); the change is audited.
 */
class PasswordController extends Controller
{
    public function update(ChangePasswordRequest $request, AccountSessionService $sessions): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        $revoked = $sessions->changePassword(
            $request->user(),
            $request->validated('password'),
            $token instanceof PersonalAccessToken ? $token : null,
            $request->hasSession() ? $request->session()->getId() : null,
        );

        return $this->success(['signed_out' => $revoked], 'Your password has been changed. Other devices were signed out.');
    }
}
