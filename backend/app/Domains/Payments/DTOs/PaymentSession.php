<?php

namespace App\Domains\Payments\DTOs;

/**
 * What place-order and retry-payment return as `next`.
 */
final readonly class PaymentSession
{
    /**
     * @param  array<string, string>  $next
     */
    public function __construct(
        public string $paymentPublicId,
        public string $gateway,
        public array $next,
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['payment_public_id' => $this->paymentPublicId, 'gateway' => $this->gateway, ...$this->next];
    }
}
