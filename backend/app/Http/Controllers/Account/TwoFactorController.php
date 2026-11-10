<?php

namespace App\Http\Controllers\Account;

use App\Domains\Identity\Services\TwoFactorService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ConfirmTwoFactorRequest;
use App\Http\Requests\Account\DisableTwoFactorRequest;
use App\Http\Requests\Account\EnrollTwoFactorRequest;
use Illuminate\Http\JsonResponse;

class TwoFactorController extends Controller
{
    public function store(EnrollTwoFactorRequest $request, TwoFactorService $twoFactor): JsonResponse
    {
        $enrollment = $twoFactor->beginEnrollment($request->user());

        return $this->success([
            'secret' => $enrollment->secret,
            'otpauth_uri' => $enrollment->otpauthUri,
        ], 'Scan the code, then confirm with a generated code.');
    }

    public function confirm(ConfirmTwoFactorRequest $request, TwoFactorService $twoFactor): JsonResponse
    {
        $recoveryCodes = $twoFactor->confirmEnrollment($request->user(), $request->string('code')->toString());

        $request->session()->put(TwoFactorService::SESSION_PASSED_KEY, now()->getTimestamp());

        return $this->success(['recovery_codes' => $recoveryCodes], 'Two-factor authentication enabled.');
    }

    public function destroy(DisableTwoFactorRequest $request, TwoFactorService $twoFactor): JsonResponse
    {
        $twoFactor->disable($request->user());

        return $this->success(message: 'Two-factor authentication disabled.');
    }
}
