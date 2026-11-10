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
}
