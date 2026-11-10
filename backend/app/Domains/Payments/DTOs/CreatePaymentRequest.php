<?php

namespace App\Domains\Payments\DTOs;

use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Enums\PaymentClient;
use App\Domains\Payments\Models\Payment;

/**
 * Everything an adapter needs to open a provider payment for one attempt. Amounts come from
 * the payment row's snapshot, already in the provider's unit; adapters never compute them.
 *
 * `returnUrl` and `cancelUrl` are built by the server for the attempt's channel (M-A6); a client
 * never supplies them. `sdkReturnUrl` is set for `app` attempts only: the value an app SDK must be
 * given on confirm (Stripe), published in `next.return_url`.
 *
 * @param  list<string>  $integrationRefs  provider method or integration identifiers (Paymob)
 */
final readonly class CreatePaymentRequest
{
    /**
     * @param  list<string>  $integrationRefs
     */
    public function __construct(
        public Payment $payment,
        public Order $order,
        public int|string $gatewayAmount,
        public string $currency,
        public string $idempotencyKey,
        public string $returnUrl,
        public string $notificationUrl,
        public string $description,
        public string $customerEmail,
        public ?string $customerName,
        public ?string $customerPhone,
        public ?string $methodCode,
        public array $integrationRefs = [],
        public ?string $cancelUrl = null,
        public PaymentClient $client = PaymentClient::Web,
        public ?string $sdkReturnUrl = null,
    ) {}
}
