<?php

namespace App\Http\Controllers\Storefront\Auth;

use App\Domains\Identity\Services\PasswordResetService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Auth\ForgotPasswordRequest;
use Illuminate\Http\JsonResponse;

class PasswordResetLinkController extends Controller
{
    public function store(ForgotPasswordRequest $request, PasswordResetService $passwordReset): JsonResponse
    {
        $passwordReset->sendResetLink($request->string('email')->toString());

        return $this->success(message: 'If that email address is registered, a reset link has been sent.');
    }
}
