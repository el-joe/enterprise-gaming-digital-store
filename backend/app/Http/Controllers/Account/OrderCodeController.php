<?php

namespace App\Http\Controllers\Account;

use App\Domains\Fulfillment\DTOs\RevealContext;
use App\Domains\Fulfillment\DTOs\RevealedCode;
use App\Domains\Fulfillment\Enums\DigitalCodeAccessActorType;
use App\Domains\Fulfillment\Enums\DigitalCodeAccessPurpose;
use App\Domains\Fulfillment\Services\CodeRevealService;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderCodeController extends Controller
{
    public function reveal(Request $request, Order $order, OrderItemUnit $unit, CodeRevealService $reveals): JsonResponse
    {
        $confirmedAt = (int) $request->session()->get(ConfirmPasswordController::SESSION_KEY, 0);
        $recent = $confirmedAt > 0 && now()->getTimestamp() - $confirmedAt <= 60 * (int) config('fulfillment.reveal_recent_auth_minutes');

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
