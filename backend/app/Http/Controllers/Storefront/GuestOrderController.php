<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Orders\Services\GuestOrderAccessService;
use App\Domains\Orders\Services\GuestOrderTokenService;
use App\Domains\Orders\Services\OrderStatusReader;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\Concerns\InteractsWithGuestOrderToken;
use App\Http\Requests\Storefront\SendGuestOtpRequest;
use App\Http\Requests\Storefront\VerifyGuestOtpRequest;
use Illuminate\Http\JsonResponse;

class GuestOrderController extends Controller
{
    use InteractsWithGuestOrderToken;

    public function __construct(private GuestOrderAccessService $access) {}

    /**
     * Always the same answer, so the endpoint cannot be used to test which emails placed orders.
     */
    public function send(SendGuestOtpRequest $request): JsonResponse
    {
        $this->access->sendCode($request->validated('order_public_id'), $request->validated('email'));

        return $this->success(message: 'If the details match an order, a code has been sent.');
    }

    /**
     * The browser session gets the order and the OTP proof; a stateless client (ADR-036) gets a
     * new guest order token holding the OTP proof (and the link proof of the token it presented).
     */
    public function verify(VerifyGuestOtpRequest $request, GuestOrderTokenService $tokens): JsonResponse
    {
        $order = $this->access->verify($request->validated('order_public_id'), $request->validated('code'));
        $data = ['order_public_id' => $order->public_id, 'email_verified' => true];

        if ($request->hasSession()) {
            $request->session()->push(OrderStatusReader::SESSION_KEY, $order->public_id);
            $request->session()->push(GuestOrderCodeController::OTP_SESSION_KEY, $order->public_id);

            return $this->success($data, 'Email verified.');
        }

        $issued = $tokens->issueAfterOtp($order, $this->presentedGuestOrderToken($request));

        return $this->withoutCaching($this->success([...$data, 'guest_order_token' => $issued->toArray()], 'Email verified.'));
    }
}
