<?php

namespace App\Domains\Payments\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

/**
 * A new payment attempt cannot be opened for this order.
 */
class PaymentAttemptRefused extends ApplicationException
{
    public function __construct(string $message, string $code = 'payment_not_allowed', int $status = 409)
    {
        parent::__construct($message, $code, $status);
    }
}
