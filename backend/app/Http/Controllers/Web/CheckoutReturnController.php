<?php

namespace App\Http\Controllers\Web;

use App\Domains\Orders\Services\OrderStatusReader;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\Concerns\InteractsWithGuestOrderToken;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Where payment providers send the browser back (`payments.return_url`,
 * `/checkout/orders/{order}/return`). The page never marks anything paid: it asks the server to
 * check with the provider (`verify-payment`, or `paypal/capture` for an approved PayPal order)
 * and then polls the order status until it is final or paid.
 */
class CheckoutReturnController extends Controller
{
    use InteractsWithGuestOrderToken;

    public function show(Request $request, string $orderPublicId, OrderStatusReader $reader): View
    {
        try {
            $order = $reader->viewableOrder($orderPublicId, $request->user(), $this->guestOrderIds($request, $orderPublicId));
        } catch (ModelNotFoundException) {
            return view('storefront.checkout-return', ['order' => null, 'gateway' => null]);
        }

        $order->loadMissing('payments.paymentGateway:id,key');
        $latest = $order->payments->sortByDesc('attempt_no')->first();

        return view('storefront.checkout-return', [
            'order' => $reader->describe($order),
            'gateway' => $latest?->paymentGateway?->key,
            'cancelled' => $request->boolean('cancelled'),
        ]);
    }
}
