<?php

namespace App\Domains\Currency\DTOs;

/**
 * The currency checkout may use. When the requested one is unusable, checkout falls back to base.
 */
final readonly class CheckoutCurrencyDecision
{
    public function __construct(
        public string $currency,
        public bool $fellBackToBase,
        public ?string $reason,
    ) {}
}
