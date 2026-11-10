<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Orders\Actions\PlaceOrderAction;
use App\Domains\Orders\DTOs\PlaceOrderInput;
use App\Domains\Orders\Services\CheckoutService;
use App\Domains\Orders\Services\OrderStatusReader;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\Concerns\InteractsWithCart;
use App\Http\Requests\Storefront\PlaceOrderRequest;
use App\Http\Requests\Storefront\ValidateCheckoutRequest;
use App\Http\Resources\Storefront\CartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    use InteractsWithCart;

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

    public function placeOrder(PlaceOrderRequest $request, PlaceOrderAction $action): JsonResponse
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
        ));

        $placed = $result->body['meta']['duplicate'] === false ? ($result->body['data']['order']['public_id'] ?? null) : null;

        if (is_string($placed)) {
            $request->session()->push(OrderStatusReader::SESSION_KEY, $placed);
        }

        $response = new JsonResponse($result->body, $result->status);

        return $result->replayed ? $response->header('Idempotency-Replayed', 'true') : $response;
    }

    public function status(Request $request, string $orderPublicId, OrderStatusReader $reader): JsonResponse
    {
        return $this->success($reader->forViewer($orderPublicId, $request->user(), $request->session()->get(OrderStatusReader::SESSION_KEY, [])));
    }
}
