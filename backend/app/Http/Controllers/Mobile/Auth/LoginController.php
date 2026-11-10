<?php

namespace App\Http\Controllers\Mobile\Auth;

use App\Domains\Identity\Services\MobileAuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\Auth\LoginRequest;
use App\Http\Resources\Account\UserResource;
use App\Http\Resources\Mobile\MobileTokenResource;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function store(LoginRequest $request, MobileAuthService $authentication): JsonResponse
    {
        $result = $authentication->login($request->toData());

        return $this->success([
            'two_factor_required' => $result->twoFactorRequired(),
            'challenge' => $result->twoFactorRequired() ? [
                'token' => $result->challengeToken,
                'expires_at' => $result->challengeExpiresAt?->toIso8601String(),
            ] : null,
            'tokens' => $result->tokens === null ? null : MobileTokenResource::make($result->tokens)->resolve(),
            'user' => $result->user === null ? null : UserResource::make($result->user)->resolve(),
        ], $result->twoFactorRequired() ? 'Two-factor authentication required.' : 'Signed in.')
            ->header('Cache-Control', 'no-store, private');
    }
}
