<?php

namespace App\Domains\Orders\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class PriceChanged extends ApplicationException
{
    public function __construct(string $currentTotal, string $currency)
    {
        $message = "The order total is now {$currentTotal} {$currency}. Please review it before paying.";

        parent::__construct($message, 'price_changed', 422, ['expected_total' => [$message]]);
    }
}
