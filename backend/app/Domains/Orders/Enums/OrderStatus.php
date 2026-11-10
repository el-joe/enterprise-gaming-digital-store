<?php

namespace App\Domains\Orders\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case PaymentProcessing = 'payment_processing';
    case Paid = 'paid';
    case Processing = 'processing';
    case SupplierPending = 'supplier_pending';
    case PendingVerification = 'pending_verification';
    case ManualReview = 'manual_review';
    case Completed = 'completed';
    case PartiallyFulfilled = 'partially_fulfilled';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case RefundPending = 'refund_pending';
    case PartiallyRefunded = 'partially_refunded';
    case Refunded = 'refunded';

    /**
     * Terminal or effectively terminal (architecture 6.2). Entering one clears the placement
     * hash, so the same cart can be bought again.
     */
    public function isClosed(): bool
    {
        return in_array($this, [self::Completed, self::PartiallyRefunded, self::Refunded, self::Cancelled, self::Failed], true);
    }

    /**
     * Statuses that mean money has arrived or goods are moving: reaching one needs a verified
     * payment on the order (rule 9).
     */
    public function requiresVerifiedPayment(): bool
    {
        return in_array($this, [self::Paid, self::Processing, self::SupplierPending, self::PendingVerification, self::Completed, self::PartiallyFulfilled], true);
    }
}
