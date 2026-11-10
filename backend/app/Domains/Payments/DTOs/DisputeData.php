<?php

namespace App\Domains\Payments\DTOs;

use App\Domains\Payments\Enums\PaymentDisputeStatus;
use App\Domains\Shared\ValueObjects\Money;
use Carbon\CarbonImmutable;

final readonly class DisputeData
{
    public function __construct(
        public string $providerDisputeId,
        public PaymentDisputeStatus $status,
        public ?string $reason,
        public Money $amount,
        public ?CarbonImmutable $evidenceDueAt,
    ) {}
}
