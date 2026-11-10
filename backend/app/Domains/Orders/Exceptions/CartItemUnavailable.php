<?php

namespace App\Domains\Orders\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class CartItemUnavailable extends ApplicationException
{
    /**
     * @param  array<string, list<string>>  $errors
     */
    public function __construct(array $errors, string $message = 'Some items in your cart cannot be bought right now.')
    {
        parent::__construct($message, 'item_unavailable', 422, $errors);
    }
}
