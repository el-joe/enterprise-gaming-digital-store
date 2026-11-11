<?php

namespace App\Http\Controllers\Mobile;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Services\PushTokenService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\RegisterPushTokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * The app device's push token (M-A18, owner decision 9, ADR-042). Behind `mobile.customer`: a
 * customer bearer token of a signed-in device. The token value is never echoed.
 */
class PushTokenController extends Controller
{
    public function __construct(private PushTokenService $pushTokens) {}

    /**
     * Register or replace this device's token. 201 for a new token, 200 when it was already known.
     */
    public function store(RegisterPushTokenRequest $request): JsonResponse
    {
        [$user, $accessToken] = $this->caller($request);
        $device = $this->pushTokens->register($user, $accessToken, $request->platform(), $request->string('token')->toString());

        return $this->success(
            ['push_token' => ['platform' => $device->platform->value, 'registered_at' => $device->updated_at?->toIso8601String()]],
            'Push notifications are on for this device.',
            status: $device->wasRecentlyCreated ? 201 : 200,
        );
    }

    /**
     * Remove this device's token. Idempotent.
     */
    public function destroy(Request $request): JsonResponse
    {
        [$user, $accessToken] = $this->caller($request);
        $this->pushTokens->unregister($user, $accessToken);

        return $this->success(message: 'Push notifications are off for this device.');
    }

    /**
     * @return array{0: User, 1: PersonalAccessToken}
     */
    private function caller(Request $request): array
    {
        /** @var User $user */
        $user = $request->user();
        /** @var PersonalAccessToken $accessToken */
        $accessToken = $user->currentAccessToken();

        return [$user, $accessToken];
    }
}
