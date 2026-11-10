<?php

namespace App\Http\Controllers\Storefront\Auth;

use App\Domains\Identity\Services\PasswordResetService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Auth\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewPasswordController extends Controller
{
    /**
     * Echo the reset parameters. The Blade reset form replaces this response in M12.
     */
    public function show(Request $request, string $token): JsonResponse
    {
        return $this->success(['token' => $token, 'email' => $request->query('email')]);
    }

    public function store(ResetPasswordRequest $request, PasswordResetService $passwordReset): JsonResponse
    {
        $passwordReset->reset($request->toData());

        return $this->success(message: 'Your password has been reset.');
    }
}
