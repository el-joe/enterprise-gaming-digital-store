<?php

namespace App\Domains\Payments\Services;

use App\Domains\Payments\Contracts\PaymentCurrencyResolver;
use App\Domains\Payments\DTOs\PaymentCurrencyQuery;
use App\Domains\Payments\DTOs\ResolvedPaymentCurrency;
use App\Domains\Payments\Exceptions\PaymentGatewayNotEligible;

/**
 * The M10 binding of the M7 contract: the chosen gateway must pass every eligibility check, and
 * the currency is one both the provider and the merchant account support (default deny). Reads
 * the database only; no provider call is made inside the placement transaction.
 */
class EligiblePaymentCurrencyResolver implements PaymentCurrencyResolver
{
    public function __construct(private PaymentEligibilityService $eligibility, private EligibilityContextFactory $contexts) {}

    public function resolve(PaymentCurrencyQuery $query): ResolvedPaymentCurrency
    {
        $result = $this->eligibility->forKey($query->gatewayKey, $this->contexts->fromQuery($query));

        if ($result === null || ! $result->isEligible()) {
            throw new PaymentGatewayNotEligible;
        }

        return new ResolvedPaymentCurrency($result->gateway->id, (string) $result->paymentCurrency);
    }
}
