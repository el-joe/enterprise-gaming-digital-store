<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Fulfillment\DTOs\RevealContext;
use App\Domains\Fulfillment\Enums\DigitalCodeAccessActorType;
use App\Domains\Fulfillment\Enums\DigitalCodeAccessPurpose;
use App\Domains\Fulfillment\Services\CodeRevealService;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Http\Controllers\Account\OrderCodeController;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Guest code access (architecture section 10): the signed link from the delivery email opens
 * the order to this browser session, and the email one-time code must also be verified in the
 * same session. Both are required for every reveal.
 */
class GuestOrderCodeController extends Controller
{
    public const string LINK_SESSION_KEY = 'guest.order_links';

    public const string OTP_SESSION_KEY = 'guest.verified_orders';

    public function access(Request $request, string $orderPublicId): JsonResponse
    {
        $request->session()->push(self::LINK_SESSION_KEY, $orderPublicId);

        return $this->success(['order_public_id' => $orderPublicId, 'otp_required' => ! in_array($orderPublicId, $request->session()->get(self::OTP_SESSION_KEY, []), true)], 'Link accepted. Verify your email to view codes.');
    }

    public function reveal(Request $request, Order $order, OrderItemUnit $unit, CodeRevealService $reveals): JsonResponse
    {
        $session = $request->session();
        $verified = in_array($order->public_id, $session->get(self::LINK_SESSION_KEY, []), true)
            && in_array($order->public_id, $session->get(self::OTP_SESSION_KEY, []), true);

        $revealed = $reveals->revealUnit($order, $unit, new RevealContext(
            DigitalCodeAccessActorType::Guest,
            null,
            DigitalCodeAccessPurpose::CustomerReveal,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            sessionVerified: $verified,
        ));

        return OrderCodeController::revealResponse($this, $revealed, $reveals->instructionsFor($unit));
    }
}
