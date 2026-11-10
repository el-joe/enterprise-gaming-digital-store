<?php

namespace App\Domains\Payments\Contracts;

use App\Domains\Payments\DTOs\PaymentSession;
use App\Domains\Payments\Models\Payment;
use App\Domains\Shared\ValueObjects\Money;

/**
 * A gateway whose "provider" is our own ledger (the wallet, BD3). There is no external party to
 * redirect to or to send a webhook: the attempt is settled server side by `settle`, which debits
 * the ledger and applies the verified result through PaymentStateApplier in the same
 * transaction. It never needs gateway credentials.
 */
interface InternalLedgerGateway extends PaymentGateway
{
    /**
     * Settle a `created` attempt. Either the debit and the paid transition both commit, or
     * neither does and the attempt is marked failed.
     */
    public function settle(Payment $payment): PaymentSession;

    /**
     * Eligibility: whether this customer could pay this amount now (advisory; `settle` decides
     * under the wallet lock).
     */
    public function canCover(?int $userId, Money $amount): bool;
}
