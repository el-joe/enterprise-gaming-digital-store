<?php

namespace App\Domains\Payments\DTOs;

use App\Domains\Payments\Enums\GatewayEventKind;

/**
 * What a verified webhook refers to, and how to find our payment: by provider references,
 * never by metadata alone (architecture 9.4).
 */
final readonly class GatewayEvent
{
    public function __construct(
        public GatewayEventKind $kind,
        public ?string $providerPaymentId = null,
        public ?string $providerOrderId = null,
        public ?string $providerCaptureId = null,
        public ?string $providerRefundId = null,
        public ?DisputeData $dispute = null,
    ) {}

    public static function ignored(): self
    {
        return new self(GatewayEventKind::Ignored);
    }
}
