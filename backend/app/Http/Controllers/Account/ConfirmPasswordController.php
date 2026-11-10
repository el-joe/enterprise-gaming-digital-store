<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ConfirmPasswordRequest;
use Illuminate\Http\JsonResponse;

/**
 * Re-authentication before sensitive actions such as revealing a code. Uses the framework's
 * `auth.password_confirmed_at` session key.
 */
class ConfirmPasswordController extends Controller
{
    public const string SESSION_KEY = 'auth.password_confirmed_at';

    public function store(ConfirmPasswordRequest $request): JsonResponse
    {
        $request->session()->put(self::SESSION_KEY, now()->getTimestamp());

        return $this->success(['confirmed_for_minutes' => (int) config('fulfillment.reveal_recent_auth_minutes')], 'Password confirmed.');
    }
}
