<?php

namespace App\Http\Controllers\Web\Account;

use App\Domains\Fulfillment\Models\DigitalCodeAccess;
use App\Domains\Identity\Services\AccountSessionService;
use App\Domains\Identity\Services\TwoFactorService;
use App\Domains\Orders\Repositories\CustomerOrderRepository;
use App\Domains\Shared\Services\QrCodeSvg;
use App\Http\Controllers\Controller;
use App\Http\Resources\Account\DeviceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Security (`/account/security`, M13, Figma "Account Security"): change password, two-factor
 * enrolment (QR code and recovery codes) and removal, code reveal history, signed-in devices
 * with revoke, and sign out everywhere else. Every change goes through the JSON routes.
 */
class SecurityController extends Controller
{
    private const int REVEAL_HISTORY = 10;

    public function show(Request $request, AccountSessionService $sessions, CustomerOrderRepository $orders): Response
    {
        $user = $request->user();

        $devices = $sessions->devices($user, null, $request->session()->getId());

        return response()->view('account.security', [
            'twoFactorEnabled' => $user->hasTwoFactorEnabled(),
            'isStaff' => $user->isStaff(),
            'devices' => DeviceResource::collection($devices)->resolve($request),
            'webSessionsListed' => config('session.driver') === 'database',
            'reveals' => $orders->recentRevealAttempts($user->id, self::REVEAL_HISTORY)->map(static fn (DigitalCodeAccess $access): array => [
                'name' => $access->digitalCode?->orderItemUnit?->orderItem?->name_snapshot,
                'masked_code' => $access->digitalCode?->masked_code,
                'granted' => (bool) $access->granted,
                'denial_reason' => $access->denial_reason,
                'at' => $access->created_at?->toIso8601String(),
            ])->all(),
        ])->header('Cache-Control', 'no-store, private');
    }

    /**
     * The QR code of a pending two-factor enrolment, as SVG (`no-store`); 404 when none is
     * pending. The secret never appears in a page or script.
     */
    public function twoFactorQr(Request $request, TwoFactorService $twoFactor, QrCodeSvg $qr): Response
    {
        $uri = $twoFactor->pendingOtpauthUri($request->user());

        abort_if($uri === null, 404);

        return response($qr->svg($uri), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
