<?php

namespace App\Http\Controllers\Web;

use App\Domains\Orders\Repositories\CustomerOrderRepository;
use App\Domains\Orders\Services\OrderStatusReader;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\Concerns\InteractsWithGuestOrderToken;
use App\Http\Controllers\Storefront\GuestOrderCodeController;
use App\Http\Resources\Account\OrderDetailResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Order result and guest order access (`/orders/{public_id}`, `noindex`).
 *
 * - The owning customer, or the browser session that placed the guest order or verified its
 *   email one-time code, sees the order (status, display and payment totals, items, reveal).
 * - A browser that opened the signed delivery link but has not verified the code sees the code
 *   form (send and verify through `/api/v1/checkout/guest/otp/*`).
 * - Anyone else gets 404, so an order's existence is never revealed.
 */
class OrderController extends Controller
{
    use InteractsWithGuestOrderToken;

    public function show(Request $request, string $orderPublicId, OrderStatusReader $reader, CustomerOrderRepository $orders): View|Response
    {
        $session = $request->session();

        try {
            $order = $reader->viewableOrder($orderPublicId, $request->user(), $this->guestOrderIds($request, $orderPublicId));
        } catch (ModelNotFoundException $missing) {
            if (! in_array($orderPublicId, $session->get(GuestOrderCodeController::LINK_SESSION_KEY, []), true)) {
                throw $missing;
            }

            return response()->view('storefront.orders.access', ['orderPublicId' => $orderPublicId])
                ->header('Cache-Control', 'no-store, private');
        }

        $isGuest = $order->user_id === null;

        return response()->view('storefront.orders.show', [
            'order' => (new OrderDetailResource($orders->loadDetail($order)))->resolve($request),
            'email' => $isGuest ? $order->guest_email : $request->user()?->email,
            'isGuest' => $isGuest,
            'canReveal' => ! $isGuest || (
                in_array($order->public_id, $session->get(GuestOrderCodeController::LINK_SESSION_KEY, []), true)
                && in_array($order->public_id, $session->get(GuestOrderCodeController::OTP_SESSION_KEY, []), true)
            ),
        ])->header('Cache-Control', 'no-store, private');
    }
}
