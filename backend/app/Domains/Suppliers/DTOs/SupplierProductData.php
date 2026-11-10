<?php

namespace App\Domains\Suppliers\DTOs;

use App\Domains\Suppliers\Enums\DenominationType;
use App\Domains\Suppliers\Enums\SupplierAvailability;

/**
 * One supplier catalog entry, validated into decimal strings. Never contains floats.
 */
final readonly class SupplierProductData
{
    /**
     * @param  list<string>  $fixedDenominations  recipient-currency denominations
     * @param  array<string, string>  $costMap  recipient denomination to sender amount
     * @param  array<string, mixed>  $raw  the supplier payload as received, for audit
     */
    public function __construct(
        public string $externalId,
        public string $name,
        public ?string $brandName,
        public ?string $countryCode,
        public ?string $categoryName,
        public ?DenominationType $denominationType,
        public ?string $currency,
        public array $fixedDenominations,
        public ?string $minDenomination,
        public ?string $maxDenomination,
        public ?string $senderCurrency,
        public array $costMap,
        public ?string $recipientToSenderRate,
        public ?string $discountPct,
        public ?string $feeFixed,
        public ?string $feePct,
        public bool $supportsPreorder,
        public bool $requiresUserId,
        public SupplierAvailability $availability,
        public array $raw,
    ) {}
}
