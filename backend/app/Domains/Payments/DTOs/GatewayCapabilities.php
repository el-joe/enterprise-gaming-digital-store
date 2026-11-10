<?php

namespace App\Domains\Payments\DTOs;

final readonly class GatewayCapabilities
{
    /**
     * @param  bool  $singleRefundInFlight  the provider has no refund idempotency, so only one refund per payment may be open
     */
    public function __construct(
        public bool $refunds,
        public bool $partialRefunds,
        public bool $capture,
        public bool $webhooks,
        public bool $refundIdempotency,
        public bool $singleRefundInFlight = false,
    ) {}
}
