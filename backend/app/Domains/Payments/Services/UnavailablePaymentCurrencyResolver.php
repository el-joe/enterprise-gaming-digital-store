<?php

namespace App\Domains\Payments\Services;

use App\Domains\Payments\Contracts\PaymentCurrencyResolver;
use App\Domains\Payments\DTOs\PaymentCurrencyQuery;
use App\Domains\Payments\DTOs\ResolvedPaymentCurrency;
use App\Domains\Payments\Exceptions\PaymentGatewayNotEligible;

/**
 * Fail-closed binding used until M10 delivers gateways and eligibility. No gateway is eligible,
 * so no order can be placed against a currency nobody has verified the gateway supports.
 */
class UnavailablePaymentCurrencyResolver implements PaymentCurrencyResolver
{
    public const string MESSAGE = 'Online payment is not available yet.';

    public function resolve(PaymentCurrencyQuery $query): ResolvedPaymentCurrency
    {
        throw new PaymentGatewayNotEligible(self::MESSAGE);
    }
}
