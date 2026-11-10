<?php

namespace App\Domains\Currency\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class ExchangeRateStale extends ApplicationException
{
    public function __construct(public readonly string $currency)
    {
        parent::__construct("The exchange rate for {$currency} is out of date, so checkout in {$currency} is unavailable.", 'rate_stale');
    }

    public function localizedMessage(): string
    {
        return __('The exchange rate for :currency is out of date, so checkout in :currency is unavailable.', ['currency' => $this->currency]);
    }
}
