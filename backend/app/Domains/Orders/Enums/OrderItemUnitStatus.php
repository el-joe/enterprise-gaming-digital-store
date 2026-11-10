<?php

namespace App\Domains\Orders\Enums;

enum OrderItemUnitStatus: string
{
    case Pending = 'pending';
    case Queued = 'queued';
    case Submitting = 'submitting';
    case SupplierPending = 'supplier_pending';
    case PendingVerification = 'pending_verification';
    case ManualTask = 'manual_task';
    case ManualReview = 'manual_review';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case RefundPending = 'refund_pending';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';
}
