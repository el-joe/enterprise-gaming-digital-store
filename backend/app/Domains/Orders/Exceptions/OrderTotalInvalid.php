<?php

namespace App\Domains\Orders\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class OrderTotalInvalid extends ApplicationException
{
    public function __construct()
    {
        parent::__construct('An order must have an amount to pay.', 'order_total_invalid', 422);
    }
}
