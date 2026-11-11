<?php

namespace App\Http\Controllers\Account;

use App\Domains\Identity\Services\AccountSessionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\SignOutOtherDevicesRequest;
use App\Http\Resources\Account\DeviceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * GET /account/devices and DELETE /account/devices/{id} (M-A5, gap G5): the customer's mobile
 * sign-ins and, with the `database` session driver, browser sessions. Both are audited.
 */
class DeviceController extends Controller
{
    public function __construct(private AccountSessionService $sessions) {}

    public function index(Request $request): JsonResponse
    {
        $devices = $this->sessions->devices($request->user(), $this->currentToken($request), $this->currentSessionId($request));

        return $this->success(['devices' => DeviceResource::collection($devices)->resolve()], meta: ['web_sessions_listed' => config('session.driver') === 'database']);
    }

    public function destroy(Request $request, string $device): JsonResponse
    {
        $this->sessions->revoke($request->user(), $device, $this->currentToken($request), $this->currentSessionId($request));

        return $this->success(message: 'The device was signed out.');
    }

    /**
     * POST /devices/sign-out-others (M13): every device except the caller is signed out.
     */
    public function signOutOthers(SignOutOtherDevicesRequest $request): JsonResponse
    {
        $revoked = $this->sessions->signOutOthers($request->user(), $this->currentToken($request), $this->currentSessionId($request));

        return $this->success(['signed_out' => $revoked], 'Every other device was signed out.');
    }

    private function currentToken(Request $request): ?PersonalAccessToken
    {
        $token = $request->user()->currentAccessToken();

        return $token instanceof PersonalAccessToken ? $token : null;
    }

    private function currentSessionId(Request $request): ?string
    {
        return $request->hasSession() ? $request->session()->getId() : null;
    }
}
