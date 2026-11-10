<?php

namespace App\Domains\Payments\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class PaymentGatewayNotEligible extends ApplicationException
{
    public function __construct(string $message = 'This payment method is not available for your order.')
    {
        parent::__construct($message, 'gateway_not_eligible', 422, ['gateway' => [$message]]);
    }
}
