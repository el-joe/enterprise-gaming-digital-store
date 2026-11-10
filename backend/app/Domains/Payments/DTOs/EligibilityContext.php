<?php

namespace App\Domains\Payments\DTOs;

use App\Domains\Customers\Enums\CustomerRiskLevel;
use App\Domains\Shared\ValueObjects\Money;

/**
 * Inputs to payment eligibility (PROJECT_ARCHITECTURE.md 12).
 */
final readonly class EligibilityContext
{
    /**
     * @param  list<int>  $productIds
     */
    public function __construct(
        public string $displayCurrency,
        public Money $totalBase,
        public string $countryCode,
        public ?string $ipCountry,
        public bool $isGuest,
        public CustomerRiskLevel $riskLevel,
        public array $productIds,
        public ?int $userId = null,
    ) {}
}
