<?php

namespace App\Http\Controllers\Account;

use App\Domains\Fulfillment\DTOs\RevealContext;
use App\Domains\Fulfillment\DTOs\RevealedCode;
use App\Domains\Fulfillment\Enums\DigitalCodeAccessActorType;
use App\Domains\Fulfillment\Enums\DigitalCodeAccessPurpose;
use App\Domains\Fulfillment\Services\CodeRevealService;
use App\Domains\Identity\Services\RecentAuthService;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class OrderCodeController extends Controller
{
    /**
     * Recent authentication comes from the session (web) or from the access token (bearer
     * clients, ADR-036), with the same window.
     */
    public function reveal(Request $request, Order $order, OrderItemUnit $unit, CodeRevealService $reveals, RecentAuthService $recentAuth): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        $recent = $request->hasSession()
            ? $recentAuth->isRecent((int) $request->session()->get(ConfirmPasswordController::SESSION_KEY, 0))
            : $token instanceof PersonalAccessToken && $recentAuth->tokenIsRecent($token);

        $revealed = $reveals->revealUnit($order, $unit, new RevealContext(
            DigitalCodeAccessActorType::Customer,
            $request->user(),
            DigitalCodeAccessPurpose::CustomerReveal,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            sessionVerified: $recent,
        ));

        return self::revealResponse($this, $revealed, $reveals->instructionsFor($unit));
    }

    /**
     * The reveal body (API_DOCUMENTATION.md section 4), never cached.
     */
    public static function revealResponse(Controller $controller, RevealedCode $revealed, ?string $instructions): JsonResponse
    {
        return (new JsonResponse([
            'success' => true,
            'message' => 'OK',
            'data' => ['code' => $revealed->code, 'pin' => $revealed->pin, 'redemption_url' => $revealed->redemptionUrl, 'instructions' => $instructions],
            'meta' => (object) [],
        ]))->header('Cache-Control', 'no-store, private')->header('Pragma', 'no-cache');
    }
}
