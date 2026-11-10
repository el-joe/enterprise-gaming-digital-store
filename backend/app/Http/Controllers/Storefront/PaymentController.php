<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Services\OrderStatusReader;
use App\Domains\Payments\Contracts\GeoIpResolver;
use App\Domains\Payments\Services\CheckoutPaymentService;
use App\Domains\Payments\Services\PaymentOptionsService;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\Concerns\InteractsWithCart;
use App\Http\Controllers\Storefront\Concerns\InteractsWithGuestOrderToken;
use App\Http\Requests\Storefront\PaymentOptionsRequest;
use App\Http\Requests\Storefront\RetryPaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use InteractsWithCart;
    use InteractsWithGuestOrderToken;

    public function __construct(private OrderStatusReader $reader, private CheckoutPaymentService $payments) {}

    /**
     * Checkout step 2: eligible gateways with display and payment amounts (advisory only).
     */
    public function options(PaymentOptionsRequest $request, PaymentOptionsService $options, GeoIpResolver $geoIp): JsonResponse
    {
        return $this->success(['options' => $options->options(
            $this->cartOwner($request),
            $this->displayCurrency($request),
            strtoupper($request->validated('country')),
            $geoIp->countryFor($request),
            $request->validated('email'),
        )]);
    }

    public function retry(RetryPaymentRequest $request, string $orderPublicId): JsonResponse
    {
        $session = $this->payments->retry($this->order($request, $orderPublicId), $request->validated('gateway'), $request->validated('method'));

        return $this->success(['next' => $session->toArray()], 'Payment started.', status: 201);
    }

    /**
     * The return page asks the server to check with the provider. Never marks paid by itself.
     */
    public function verify(Request $request, string $orderPublicId): JsonResponse
    {
        $order = $this->order($request, $orderPublicId);
        $this->payments->verify($order);

        return $this->success($this->reader->describe($order->refresh()));
    }

    public function capture(Request $request, string $orderPublicId): JsonResponse
    {
        $order = $this->order($request, $orderPublicId);
        $this->payments->capture($order);

        return $this->success($this->reader->describe($order->refresh()));
    }

    private function order(Request $request, string $orderPublicId): Order
    {
        return $this->reader->viewableOrder($orderPublicId, $request->user(), $this->guestOrderIds($request, $orderPublicId));
    }
}
