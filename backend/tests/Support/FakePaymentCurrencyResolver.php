<?php

namespace Tests\Support;

use App\Domains\Payments\Contracts\PaymentCurrencyResolver;
use App\Domains\Payments\DTOs\PaymentCurrencyQuery;
use App\Domains\Payments\DTOs\ResolvedPaymentCurrency;
use App\Domains\Payments\Exceptions\PaymentGatewayNotEligible;

/**
 * Test double for the M10 contract: one known gateway key, charged in a fixed currency or in the
 * display currency. Lives under tests/ only.
 */
class FakePaymentCurrencyResolver implements PaymentCurrencyResolver
{
    /** @var list<PaymentCurrencyQuery> */
    public array $queries = [];

    public function __construct(
        private int $gatewayId,
        private string $gatewayKey = 'fakepay',
        private ?string $currency = null,
    ) {}

    public function resolve(PaymentCurrencyQuery $query): ResolvedPaymentCurrency
    {
        $this->queries[] = $query;

        if ($query->gatewayKey !== $this->gatewayKey) {
            throw new PaymentGatewayNotEligible;
        }

        return new ResolvedPaymentCurrency($this->gatewayId, $this->currency ?? $query->displayCurrency);
    }
}
