<?php

namespace App\Domains\Payments\Enums;

enum PaymentTransactionStatus: string
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
}
