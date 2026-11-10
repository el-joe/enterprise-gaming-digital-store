<?php

namespace App\Domains\Payments\Enums;

/**
 * Which client started a payment attempt (M-A6). It only selects the server-built return and
 * cancel URLs; it never changes what is charged or how a payment is verified.
 */
enum PaymentClient: string
{
    case Web = 'web';
    case App = 'app';
}
