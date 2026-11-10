<?php

namespace App\Http\Controllers\Mobile\Auth;

use App\Domains\Identity\Services\MobileAuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\Auth\TwoFactorChallengeRequest;
use App\Http\Resources\Account\UserResource;
use App\Http\Resources\Mobile\MobileTokenResource;
use Illuminate\Http\JsonResponse;

class TwoFactorChallengeController extends Controller
{
    public function store(TwoFactorChallengeRequest $request, MobileAuthService $authentication): JsonResponse
    {
        $signedIn = $authentication->completeTwoFactorChallenge(
            $request->string('challenge_token')->toString(),
            $request->string('code')->toString() ?: null,
            $request->string('recovery_code')->toString() ?: null,
            $request->ip(),
        );

        return $this->success([
            'tokens' => MobileTokenResource::make($signedIn['tokens'])->resolve(),
            'user' => UserResource::make($signedIn['user'])->resolve(),
        ], 'Signed in.')->header('Cache-Control', 'no-store, private');
    }
}
