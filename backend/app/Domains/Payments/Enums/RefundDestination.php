<?php

namespace App\Domains\Payments\Enums;

enum RefundDestination: string
{
    case OriginalMethod = 'original_method';
    case Wallet = 'wallet';
}
