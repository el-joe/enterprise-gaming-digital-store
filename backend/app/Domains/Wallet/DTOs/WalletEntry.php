<?php

namespace App\Domains\Wallet\DTOs;

use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Wallet\Enums\WalletTransactionType;

/**
 * One movement to post to a customer's wallet. `amount` is always positive; the type decides
 * the sign (credits add, debits and reversals subtract). The idempotency key is unique per
 * wallet: posting the same key again returns the first entry and changes nothing.
 */
final readonly class WalletEntry
{
    public function __construct(
        public int $userId,
        public WalletTransactionType $type,
        public Money $amount,
        public string $idempotencyKey,
        public ?string $referenceType = null,
        public ?int $referenceId = null,
        public ?string $note = null,
        public ?int $createdBy = null,
    ) {}

    public function isCredit(): bool
    {
        return in_array($this->type, [WalletTransactionType::CreditRefund, WalletTransactionType::CreditAdmin], true);
    }

    /**
     * The signed amount stored in the ledger.
     */
    public function signedAmount(): Money
    {
        return $this->isCredit() ? $this->amount : $this->amount->negated();
    }
}
