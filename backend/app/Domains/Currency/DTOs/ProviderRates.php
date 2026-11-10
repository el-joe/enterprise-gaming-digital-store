<?php

namespace App\Domains\Currency\DTOs;

use App\Domains\Shared\ValueObjects\Rate;
use Carbon\CarbonImmutable;

final readonly class ProviderRates
{
    /**
     * @param  array<string, Rate>  $rates  keyed by quote currency
     */
    public function __construct(
        public string $base,
        public string $source,
        public CarbonImmutable $capturedAt,
        public array $rates,
    ) {}
}
