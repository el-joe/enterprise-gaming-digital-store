<?php

namespace App\Domains\Currency\DTOs;

use App\Domains\Currency\Enums\DisplayCurrencySource;

/**
 * The currency a request browses in, and why. `country` is the country used for the
 * country-currency step (profile country, else GeoIP), or null when none was known or an explicit
 * choice decided first.
 */
final readonly class DisplayCurrencyDecision
{
    public function __construct(
        public string $currency,
        public DisplayCurrencySource $source,
        public ?string $country = null,
    ) {}
}
