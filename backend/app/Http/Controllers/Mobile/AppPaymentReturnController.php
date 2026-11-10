<?php

namespace App\Http\Controllers\Mobile;

use App\Domains\Orders\Repositories\OrderRepository;
use App\Domains\Payments\Services\PaymentReturnUrlBuilder;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

/**
 * The app return links `/app/payments/return/{order}` and `/app/payments/cancel/{order}` (M-A6,
 * ADR-039). When the app is installed and the link verified, the operating system opens the app
 * and this code never runs. When it runs, the link was opened in a browser: it redirects to the
 * web return page for the order, which asks the server to verify, exactly as a web return does.
 *
 * It reads nothing but the order public id: every query parameter (Paymob's `success`, the
 * attempt id, anything else) is ignored and not forwarded, nothing is verified or changed here,
 * and nothing is ever marked paid. An unknown order is 404.
 */
class AppPaymentReturnController extends Controller
{
    public function __construct(private PaymentReturnUrlBuilder $urls, private OrderRepository $orders) {}

    public function return(string $order): RedirectResponse
    {
        return $this->toWeb($order, $this->urls->webReturnUrl(...));
    }

    public function cancel(string $order): RedirectResponse
    {
        return $this->toWeb($order, $this->urls->webCancelUrl(...));
    }

    /**
     * @param  callable(string): string  $target
     */
    private function toWeb(string $orderPublicId, callable $target): RedirectResponse
    {
        abort_if($this->orders->findByPublicId($orderPublicId) === null, 404);

        return redirect()->away($target($orderPublicId))->withHeaders([
            'Cache-Control' => 'no-store, private',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
