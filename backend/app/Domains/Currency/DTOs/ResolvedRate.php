<?php

namespace App\Domains\Currency\DTOs;

use App\Domains\Shared\ValueObjects\Rate;
use Carbon\CarbonImmutable;

final readonly class ResolvedRate
{
    /**
     * @param  Rate  $rate  the customer-facing rate: provider with markup, or the manual override
     * @param  Rate  $rateWithoutMarkup  the provider or manual rate, for costs and explicit prices
     */
    public function __construct(
        public string $base,
        public string $quote,
        public Rate $rate,
        public Rate $rateWithoutMarkup,
        public string $source,
        public ?CarbonImmutable $fetchedAt,
        public bool $isManual,
        public bool $isStale,
        public ?int $historyId,
    ) {}
}
