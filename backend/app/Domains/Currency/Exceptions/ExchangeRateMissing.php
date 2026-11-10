<?php

namespace App\Domains\Currency\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class ExchangeRateMissing extends ApplicationException
{
    public function __construct(public readonly string $currency)
    {
        parent::__construct("No exchange rate is available for {$currency}.", 'rate_missing');
    }

    public function localizedMessage(): string
    {
        return __('No exchange rate is available for :currency.', ['currency' => $this->currency]);
    }
}
