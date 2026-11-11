<?php

namespace App\Http\Controllers\Mobile\Auth;

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\BiometricAuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\Auth\ConfirmBiometricRequest;
use App\Http\Requests\Mobile\Auth\RegisterBiometricKeyRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Biometric re-authentication of the mobile app (owner decision 6, OD-1, ADR-040). Every route is
 * behind `mobile.customer`: a customer bearer token of a signed-in device.
 */
class BiometricController extends Controller
{
    public function __construct(private BiometricAuthService $biometrics) {}

    /**
     * Register (or replace) this device's public key. Needs a recent password confirmation.
     */
    public function store(RegisterBiometricKeyRequest $request): JsonResponse
    {
        [$user, $token] = $this->caller($request);
        $key = $this->biometrics->register($user, $token, $request->string('public_key')->toString(), $request->ip());

        return $this->success(['biometric_key' => ['id' => $key->public_id, 'algorithm' => $key->algorithm, 'created_at' => $key->created_at?->toIso8601String()]], 'Biometric unlock is on.', status: 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        [$user, $token] = $this->caller($request);
        $this->biometrics->revokeCurrent($user, $token);

        return $this->success(message: 'Biometric unlock is off.');
    }

    public function challenge(Request $request): JsonResponse
    {
        [$user, $token] = $this->caller($request);

        return $this->success(['challenge' => $this->biometrics->issueChallenge($user, $token)->toArray()])
            ->header('Cache-Control', 'no-store, private');
    }

    /**
     * Same response as `POST /account/confirm-password`.
     */
    public function confirm(ConfirmBiometricRequest $request): JsonResponse
    {
        [$user, $token] = $this->caller($request);
        $minutes = $this->biometrics->confirm($user, $token, $request->string('challenge')->toString(), $request->string('signature')->toString(), $request->ip());

        return $this->success(['confirmed_for_minutes' => $minutes], 'Identity confirmed.');
    }

    /**
     * @return array{0: User, 1: PersonalAccessToken}
     */
    private function caller(Request $request): array
    {
        /** @var User $user */
        $user = $request->user();
        /** @var PersonalAccessToken $token */
        $token = $user->currentAccessToken();

        return [$user, $token];
    }
}
