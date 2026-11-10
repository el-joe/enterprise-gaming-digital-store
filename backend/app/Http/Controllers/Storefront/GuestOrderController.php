<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Orders\Repositories\CustomerOrderRepository;
use App\Domains\Orders\Services\GuestOrderAccessService;
use App\Domains\Orders\Services\GuestOrderTokenService;
use App\Domains\Orders\Services\OrderStatusReader;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\Concerns\InteractsWithGuestOrderToken;
use App\Http\Requests\Storefront\SendGuestOtpRequest;
use App\Http\Requests\Storefront\VerifyGuestOtpRequest;
use App\Http\Resources\Account\OrderDetailResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    /**
     * The guest order detail (M-A5): the account detail shape for a guest order. It needs the
     * proof that opens the order for status and payment calls, not the reveal proof: the browser
     * session that placed the order or verified the email one-time code, or a stateless
     * `X-Guest-Order-Token` from placement or OTP verification (a link-only token opens nothing).
     * Anything else, and any customer's order, is 404. `meta.guest_access` tells the client which
     * reveal proofs it already holds (a reveal needs both).
     */
    public function show(Request $request, string $orderPublicId, OrderStatusReader $reader, GuestOrderTokenService $tokens, CustomerOrderRepository $orders): JsonResponse
    {
        $order = $reader->viewableOrder($orderPublicId, null, $this->guestOrderIds($request, $orderPublicId));

        if ($request->hasSession()) {
            $access = [
                'link_verified' => in_array($order->public_id, $request->session()->get(GuestOrderCodeController::LINK_SESSION_KEY, []), true),
                'otp_verified' => in_array($order->public_id, $request->session()->get(GuestOrderCodeController::OTP_SESSION_KEY, []), true),
            ];
        } else {
            $token = $tokens->live($order, $this->presentedGuestOrderToken($request));
            $access = ['link_verified' => $token?->link_verified_at !== null, 'otp_verified' => $token?->otp_verified_at !== null];
        }

        return $this->withoutCaching($this->success(
            ['order' => OrderDetailResource::make($orders->loadDetail($order))->resolve()],
            meta: ['guest_access' => $access],
        ));
    }
}
