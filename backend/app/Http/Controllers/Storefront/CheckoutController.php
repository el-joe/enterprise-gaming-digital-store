<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Affiliates\Services\AffiliateTracker;
use App\Domains\Orders\Actions\PlaceOrderAction;
use App\Domains\Orders\DTOs\PlaceOrderInput;
use App\Domains\Orders\Services\CheckoutService;
use App\Domains\Orders\Services\GuestOrderTokenService;
use App\Domains\Orders\Services\OrderStatusReader;
use App\Domains\Payments\Contracts\GeoIpResolver;
use App\Domains\Payments\Services\CheckoutPaymentService;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\Concerns\InteractsWithCart;
use App\Http\Controllers\Storefront\Concerns\InteractsWithGuestOrderToken;
use App\Http\Requests\Storefront\PlaceOrderRequest;
use App\Http\Requests\Storefront\ValidateCheckoutRequest;
use App\Http\Resources\Storefront\CartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    use InteractsWithCart;
    use InteractsWithGuestOrderToken;

    /**
     * Step 1: reprice with checkout rules and revalidate. A stale rate is refused here.
     */
    public function check(ValidateCheckoutRequest $request, CheckoutService $checkout): JsonResponse
    {
        $result = $checkout->validate(
            $this->cartOwner($request),
            $this->displayCurrency($request),
            $request->validated('country'),
            $request->validated('email'),
        );

        return $this->success(['cart' => (new CartResource($result['quote']))->resolve()], 'Ready for checkout.');
    }

    /**
     * Step 3, then step 4: once the order has committed, the first payment attempt is created
     * at the provider (outside the placement transaction) and returned as `next`. A replay
     * returns the stored placement response, whose `next` is null, as is `next` when the first
     * attempt could not be started; the client then uses retry-payment. A new order is linked to
     * the browser's last affiliate click before payment starts (M11). A wallet attempt is settled
     * at once, so its `next` is `{ type: "wallet" }` and the order is already paid.
     *
     * A stateless guest (ADR-036) receives `guest_order_token` instead of the session order list,
     * also on a replay (the stored body never holds a token, so a client whose first response was
     * lost can still follow its order).
     */
    public function placeOrder(PlaceOrderRequest $request, PlaceOrderAction $action, GeoIpResolver $geoIp, CheckoutPaymentService $payments, AffiliateTracker $affiliates, GuestOrderTokenService $guestTokens): JsonResponse
    {
        $result = $action->handle(new PlaceOrderInput(
            owner: $this->cartOwner($request),
            idempotencyKey: $request->validated('idempotency_key'),
            requestHash: $request->fingerprint(),
            displayCurrency: $this->displayCurrency($request),
            confirmedCurrency: strtoupper($request->validated('currency')),
            email: $request->validated('email'),
            country: $request->validated('country'),
            gateway: $request->validated('gateway'),
            method: $request->validated('method'),
            expectedTotal: $request->validated('expected_total'),
            marketingConsent: (bool) $request->validated('consents.marketing', false),
            gaClientId: $request->validated('ga_client_id'),
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            locale: app()->getLocale(),
            ipCountry: $geoIp->countryFor($request),
        ));

        $placed = $result->body['meta']['duplicate'] === false ? ($result->body['data']['order']['public_id'] ?? null) : null;

        $body = $result->body;

        $issuedGuestToken = null;

        if (is_string($placed) && $request->hasSession()) {
            $request->session()->push(OrderStatusReader::SESSION_KEY, $placed);
        } elseif (is_string($placed) && $request->user() === null) {
            $issuedGuestToken = $guestTokens->issueForPlacement($placed);
            $body['data']['guest_order_token'] = $issuedGuestToken->toArray();
        }

        if (is_string($placed) && ! $result->replayed && $result->createdOrderPublicId !== null) {
            $affiliates->attribute($placed, $request->cookie((string) config('affiliates.cookie')));
            $session = $payments->startAfterPlacement($placed, $request->validated('method'), $request->paymentClient())['session'];

            if ($session !== null) {
                $body['data']['next'] = $session->toArray();
            }
        }

        $response = new JsonResponse($body, $result->status);

        if ($issuedGuestToken !== null) {
            $this->withoutCaching($response);
        }

        return $result->replayed ? $response->header('Idempotency-Replayed', 'true') : $response;
    }

    public function status(Request $request, string $orderPublicId, OrderStatusReader $reader): JsonResponse
    {
        return $this->success($reader->forViewer($orderPublicId, $request->user(), $this->guestOrderIds($request, $orderPublicId)));
    }
}
