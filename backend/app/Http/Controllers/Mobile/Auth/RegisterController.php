<?php

namespace App\Http\Controllers\Mobile\Auth;

use App\Domains\Identity\Services\MobileAuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\Auth\RegisterRequest;
use App\Http\Resources\Account\UserResource;
use App\Http\Resources\Mobile\MobileTokenResource;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function store(RegisterRequest $request, MobileAuthService $authentication): JsonResponse
    {
        $registered = $authentication->register($request->toData(), $request->string('device_name')->toString(), $request->ip());

        return $this->success(
            [
                'tokens' => MobileTokenResource::make($registered['tokens'])->resolve(),
                'user' => UserResource::make($registered['user'])->resolve(),
            ],
            'Account created. Please verify your email address.',
            status: 201,
        )->header('Cache-Control', 'no-store, private');
    }
}
