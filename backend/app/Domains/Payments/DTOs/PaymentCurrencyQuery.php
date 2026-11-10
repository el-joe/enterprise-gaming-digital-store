<?php

namespace App\Domains\Payments\DTOs;

use App\Domains\Shared\ValueObjects\Money;

/**
 * Everything eligibility and currency resolution may consider for one placement.
 */
final readonly class PaymentCurrencyQuery
{
    /**
     * @param  list<int>  $productIds
     */
    public function __construct(
        public string $gatewayKey,
        public ?string $methodCode,
        public string $displayCurrency,
        public Money $totalBase,
        public string $countryCode,
        public ?string $ipCountry,
        public bool $isGuest,
        public ?int $userId,
        public array $productIds,
    ) {}
}
