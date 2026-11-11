<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Fulfillment\DTOs\RevealContext;
use App\Domains\Fulfillment\Enums\DeviceIntegrity;
use App\Domains\Fulfillment\Enums\DigitalCodeAccessActorType;
use App\Domains\Fulfillment\Enums\DigitalCodeAccessPurpose;
use App\Domains\Fulfillment\Services\CodeRevealService;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\GuestOrderTokenService;
use App\Http\Controllers\Account\OrderCodeController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\Concerns\InteractsWithGuestOrderToken;
use App\Http\Middleware\Storefront\SetStorefrontLocale;
use App\Http\Support\LocalizedRoutes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Guest code access (architecture section 10): the signed link from the delivery email opens
 * the order to this browser session, and the email one-time code must also be verified in the
 * same session. Both are required for every reveal. A stateless client (ADR-036) holds the same
 * two proofs on its guest order token (`X-Guest-Order-Token`) instead of the session.
 */
class GuestOrderCodeController extends Controller
{
    use InteractsWithGuestOrderToken;

    public const string LINK_SESSION_KEY = 'guest.order_links';

    public const string OTP_SESSION_KEY = 'guest.verified_orders';

    public function __construct(private GuestOrderTokenService $tokens) {}

    /**
     * A browser opening the delivery email link (M12) is sent on to the guest order page, which
     * asks for the email one-time code; JSON clients get the envelope as before.
     */
    public function access(Request $request, string $orderPublicId): JsonResponse|RedirectResponse
    {
        if ($request->hasSession()) {
            $request->session()->push(self::LINK_SESSION_KEY, $orderPublicId);

            if (! $request->expectsJson()) {
                return redirect()->to(LocalizedRoutes::url('storefront.orders.show', ['orderPublicId' => $orderPublicId], $request->session()->get(SetStorefrontLocale::SESSION_KEY)))
                    ->header('Cache-Control', 'no-store, private')
                    ->header('Referrer-Policy', 'no-referrer');
            }

            return $this->success(['order_public_id' => $orderPublicId, 'otp_required' => ! in_array($orderPublicId, $request->session()->get(self::OTP_SESSION_KEY, []), true)], 'Link accepted. Verify your email to view codes.');
        }

        $order = $this->tokens->guestOrder($orderPublicId);
        $presented = $this->presentedGuestOrderToken($request);
        $issued = $this->tokens->recordLinkProof($order, $presented);

        return $this->withoutCaching($this->success([
            'order_public_id' => $orderPublicId,
            'otp_required' => ! $this->tokens->provesGuestIdentity($order, $issued?->token ?? $presented),
            'guest_order_token' => $issued?->toArray(),
        ], 'Link accepted. Verify your email to view codes.'));
    }

    public function reveal(Request $request, Order $order, OrderItemUnit $unit, CodeRevealService $reveals): JsonResponse
    {
        if ($request->hasSession()) {
            $session = $request->session();
            $verified = in_array($order->public_id, $session->get(self::LINK_SESSION_KEY, []), true)
                && in_array($order->public_id, $session->get(self::OTP_SESSION_KEY, []), true);
        } else {
            $verified = $this->tokens->provesGuestIdentity($order, $this->presentedGuestOrderToken($request));
        }

        $revealed = $reveals->revealUnit($order, $unit, new RevealContext(
            DigitalCodeAccessActorType::Guest,
            null,
            DigitalCodeAccessPurpose::CustomerReveal,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            sessionVerified: $verified,
            deviceIntegrity: $request->hasSession() ? null : DeviceIntegrity::fromHeader($request->headers->get(DeviceIntegrity::HEADER)),
        ));

        return OrderCodeController::revealResponse($this, $revealed, $reveals->instructionsFor($unit));
    }
}
