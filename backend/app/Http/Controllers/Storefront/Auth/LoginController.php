<?php

namespace App\Http\Controllers\Storefront\Auth;

use App\Domains\Identity\Services\AuthenticationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Auth\LoginRequest;
use App\Http\Resources\Account\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function store(LoginRequest $request, AuthenticationService $authentication): JsonResponse
    {
        $result = $authentication->login($request->toData(), $request->session());

        return $this->success([
            'two_factor_required' => $result->twoFactorRequired,
            'two_factor_enrollment_required' => $result->twoFactorEnrollmentRequired,
            'user' => $result->user === null ? null : UserResource::make($result->user)->resolve(),
        ], $result->twoFactorRequired ? 'Two-factor authentication required.' : 'Signed in.');
    }

    public function destroy(Request $request, AuthenticationService $authentication): JsonResponse
    {
        $authentication->logout($request->session());

        return $this->success(message: 'Signed out.');
    }
}
