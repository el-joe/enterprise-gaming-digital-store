<?php

namespace App\Domains\Currency\Contracts;

use App\Domains\Currency\DTOs\ProviderRates;
use App\Domains\Currency\Exceptions\ExchangeRateProviderFailed;

interface ExchangeRateProvider
{
    /**
     * Stable provider name stored as the rate source.
     */
    public function name(): string;

    /**
     * Fetch current rates against the given base currency.
     *
     * @param  list<string>  $symbols
     *
     * @throws ExchangeRateProviderFailed
     */
    public function latest(string $base, array $symbols): ProviderRates;
}
