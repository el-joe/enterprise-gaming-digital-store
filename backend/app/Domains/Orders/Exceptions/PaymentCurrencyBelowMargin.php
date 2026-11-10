<?php

namespace App\Domains\Orders\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class PaymentCurrencyBelowMargin extends ApplicationException
{
    public function __construct()
    {
        parent::__construct(
            'This payment method cannot be used for this order at the confirmed price. Pay in the displayed currency or choose another payment method.',
            'payment_currency_below_margin',
        );
    }
}
