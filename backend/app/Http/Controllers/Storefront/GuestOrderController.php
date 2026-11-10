<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Orders\Services\GuestOrderAccessService;
use App\Domains\Orders\Services\OrderStatusReader;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\SendGuestOtpRequest;
use App\Http\Requests\Storefront\VerifyGuestOtpRequest;
use Illuminate\Http\JsonResponse;

class GuestOrderController extends Controller
{
    public function __construct(private GuestOrderAccessService $access) {}

    /**
     * Always the same answer, so the endpoint cannot be used to test which emails placed orders.
     */
    public function send(SendGuestOtpRequest $request): JsonResponse
    {
        $this->access->sendCode($request->validated('order_public_id'), $request->validated('email'));

        return $this->success(message: 'If the details match an order, a code has been sent.');
    }

    public function verify(VerifyGuestOtpRequest $request): JsonResponse
    {
        $order = $this->access->verify($request->validated('order_public_id'), $request->validated('code'));

        $request->session()->push(OrderStatusReader::SESSION_KEY, $order->public_id);

        return $this->success(['order_public_id' => $order->public_id, 'email_verified' => true], 'Email verified.');
    }
}
