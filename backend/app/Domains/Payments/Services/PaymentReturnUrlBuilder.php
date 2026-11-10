<?php

namespace App\Domains\Payments\Services;

use App\Domains\Orders\Models\Order;
use App\Domains\Payments\DTOs\PaymentReturnUrls;
use App\Domains\Payments\Enums\PaymentClient;
use App\Domains\Payments\Exceptions\GatewayNotConfigured;
use App\Domains\Payments\Models\Payment;

/**
 * Builds the provider return and cancel URLs from configuration only (M-A6, ADR-039). Nothing a
 * client sends is ever part of them except the channel, and the channel only picks one of the
 * configured templates. The order public id is the only order identifier in a URL; the attempt
 * public id is added for the app (it is not a credential: status, verify and capture still need
 * the session, the bearer token or the guest order token).
 *
 * - `web`: `payments.return_url` and `payments.cancel_url` (the M12 return page), as before.
 * - `app`: `payments.app_return_url` and `payments.app_cancel_url`, paths on `APP_URL`, which must
 *   be HTTPS in production so the paths can be verified app links (`/.well-known` files).
 */
class PaymentReturnUrlBuilder
{
    /**
     * @throws GatewayNotConfigured when an app template is not a path on the store domain
     */
    public function for(Order $order, Payment $payment, PaymentClient $client): PaymentReturnUrls
    {
        if ($client === PaymentClient::Web) {
            return new PaymentReturnUrls($this->webReturnUrl($order->public_id), $this->webCancelUrl($order->public_id));
        }

        $returnUrl = $this->appUrl('payments.app_return_url', $order->public_id, $payment->public_id);
        $sdkReturnUrl = config('payments.stripe_app_return_url');

        return new PaymentReturnUrls(
            returnUrl: $returnUrl,
            cancelUrl: $this->appUrl('payments.app_cancel_url', $order->public_id, $payment->public_id),
            sdkReturnUrl: is_string($sdkReturnUrl) && $sdkReturnUrl !== '' ? $sdkReturnUrl : $returnUrl,
        );
    }

    /**
     * The web return page for an order (unchanged from M10).
     */
    public function webReturnUrl(string $orderPublicId): string
    {
        return url(str_replace('{order}', $orderPublicId, (string) config('payments.return_url')));
    }

    /**
     * Where a cancelled provider page sends the browser; the return page when not configured.
     */
    public function webCancelUrl(string $orderPublicId): string
    {
        $template = config('payments.cancel_url');

        if (! is_string($template) || $template === '') {
            return $this->webReturnUrl($orderPublicId);
        }

        return url(str_replace('{order}', $orderPublicId, $template));
    }

    private function appUrl(string $key, string $orderPublicId, string $paymentPublicId): string
    {
        $template = (string) config($key);
        $base = rtrim((string) config('app.url'), '/');

        if (! str_starts_with($template, '/') || str_starts_with($template, '//')) {
            throw new GatewayNotConfigured('The app return URL must be a path on the store domain.');
        }

        if (app()->isProduction() && ! str_starts_with($base, 'https://')) {
            throw new GatewayNotConfigured('The app return URL needs an HTTPS APP_URL.');
        }

        return $base.strtr($template, ['{order}' => rawurlencode($orderPublicId), '{attempt}' => rawurlencode($paymentPublicId)]);
    }
}
