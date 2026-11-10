<?php

namespace App\Http\Controllers\Storefront\Auth;

use App\Domains\Identity\Services\RegistrationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Auth\RegisterRequest;
use App\Http\Resources\Account\UserResource;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function store(RegisterRequest $request, RegistrationService $registration): JsonResponse
    {
        $user = $registration->register($request->toData());

        return $this->success(
            ['user' => UserResource::make($user)->resolve()],
            'Account created. Please verify your email address.',
            status: 201,
        );
    }
}
