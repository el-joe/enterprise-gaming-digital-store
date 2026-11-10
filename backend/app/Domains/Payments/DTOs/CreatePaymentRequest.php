<?php

namespace App\Domains\Payments\DTOs;

use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Models\Payment;

/**
 * Everything an adapter needs to open a provider payment for one attempt. Amounts come from
 * the payment row's snapshot, already in the provider's unit; adapters never compute them.
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
    ) {}
}
