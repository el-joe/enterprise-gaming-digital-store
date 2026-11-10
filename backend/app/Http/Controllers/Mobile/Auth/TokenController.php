<?php

namespace App\Http\Controllers\Mobile\Auth;

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\MobileTokenService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\Auth\RefreshTokenRequest;
use App\Http\Resources\Mobile\MobileTokenResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class TokenController extends Controller
{
    public function refresh(RefreshTokenRequest $request, MobileTokenService $tokens): JsonResponse
    {
        $issued = $tokens->refresh($request->string('refresh_token')->toString(), $request->ip());

        return $this->success(['tokens' => MobileTokenResource::make($issued)->resolve()], 'Tokens refreshed.')
            ->header('Cache-Control', 'no-store, private');
    }

    public function destroy(Request $request, MobileTokenService $tokens): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var PersonalAccessToken $accessToken */
        $accessToken = $user->currentAccessToken();

        $tokens->revokeCurrent($accessToken);

        return $this->success(message: 'Signed out.');
    }

    public function destroyAll(Request $request, MobileTokenService $tokens): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $tokens->revokeAll($user);

        return $this->success(message: 'Signed out of all devices.');
    }
}
