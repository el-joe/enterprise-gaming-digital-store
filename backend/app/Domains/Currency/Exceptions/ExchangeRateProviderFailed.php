<?php

namespace App\Domains\Currency\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

/**
 * Carries no provider detail: the underlying URL contains the app_id and must not reach logs.
 */
class ExchangeRateProviderFailed extends ApplicationException
{
    public function __construct(string $message = 'The exchange rate provider is unavailable.')
    {
        parent::__construct($message, 'rates_provider_unavailable', 503);
    }
}
