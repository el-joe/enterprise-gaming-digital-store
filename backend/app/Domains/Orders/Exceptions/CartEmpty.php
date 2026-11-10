<?php

namespace App\Domains\Orders\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class CartEmpty extends ApplicationException
{
    public function __construct()
    {
        parent::__construct('Your cart is empty.', 'cart_empty', 422);
    }
}
