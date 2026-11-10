<?php

namespace App\Http\Controllers\Storefront\Auth;

use App\Domains\Identity\Services\AuthenticationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Auth\TwoFactorChallengeRequest;
use App\Http\Resources\Account\UserResource;
use Illuminate\Http\JsonResponse;

class TwoFactorChallengeController extends Controller
{
    public function store(TwoFactorChallengeRequest $request, AuthenticationService $authentication): JsonResponse
    {
        $user = $authentication->completeTwoFactorChallenge(
            $request->string('code')->toString() ?: null,
            $request->string('recovery_code')->toString() ?: null,
            $request->ip(),
            $request->session(),
        );

        return $this->success(['user' => UserResource::make($user)->resolve()], 'Signed in.');
    }
}
