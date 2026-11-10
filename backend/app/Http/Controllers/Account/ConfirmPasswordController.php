<?php

namespace App\Http\Controllers\Account;

use App\Domains\Identity\Services\RecentAuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ConfirmPasswordRequest;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Re-authentication before sensitive actions such as revealing a code. The web uses the
 * framework's `auth.password_confirmed_at` session key; a bearer client (M-A3, ADR-036) records
 * the confirmation on its access token.
 */
class ConfirmPasswordController extends Controller
{
    public const string SESSION_KEY = RecentAuthService::SESSION_KEY;

    public function store(ConfirmPasswordRequest $request, RecentAuthService $recentAuth): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($request->hasSession()) {
            $request->session()->put(self::SESSION_KEY, now()->getTimestamp());
        } elseif ($token instanceof PersonalAccessToken) {
            $recentAuth->confirmToken($token);
        }

        return $this->success(['confirmed_for_minutes' => $recentAuth->windowMinutes()], 'Password confirmed.');
    }
}
