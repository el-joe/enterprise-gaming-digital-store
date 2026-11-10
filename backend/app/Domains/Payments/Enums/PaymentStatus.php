<?php

namespace App\Domains\Payments\Enums;

enum PaymentStatus: string
{
    case Created = 'created';
    case RequiresAction = 'requires_action';
    case Processing = 'processing';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case PartiallyRefunded = 'partially_refunded';
    case Refunded = 'refunded';
    case DuplicateCapture = 'duplicate_capture';
}
