<?php

namespace App\Domains\Payments\Enums;

enum PaymentTransactionType: string
{
    case Authorization = 'authorization';
    case Capture = 'capture';
    case Refund = 'refund';
    case Void = 'void';
    case Chargeback = 'chargeback';
    case ChargebackReversal = 'chargeback_reversal';
    case FeeAdjustment = 'fee_adjustment';
}
