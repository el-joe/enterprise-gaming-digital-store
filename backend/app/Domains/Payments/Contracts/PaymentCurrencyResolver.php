<?php

namespace App\Domains\Payments\Contracts;

use App\Domains\Payments\DTOs\PaymentCurrencyQuery;
use App\Domains\Payments\DTOs\ResolvedPaymentCurrency;
use App\Domains\Payments\Exceptions\PaymentGatewayNotEligible;

/**
 * Decides which gateway account and which currency an order is charged in
 * (PROJECT_ARCHITECTURE.md 11.2 and 12). M10 implements it, including the eligibility rules;
 * until then the container binds UnavailablePaymentCurrencyResolver, which refuses every gateway.
 *
 * Contract for implementers:
 * - Never return a currency the gateway account does not support (default deny).
 * - Return only the gateway and currency. Order code converts the base total once, with a fresh
 *   rate, and snapshots the result; the implementation must not compute charged amounts.
 * - No HTTP calls: this runs inside the placement transaction.
 */
interface PaymentCurrencyResolver
{
    /**
     * @throws PaymentGatewayNotEligible
     */
    public function resolve(PaymentCurrencyQuery $query): ResolvedPaymentCurrency;
}
