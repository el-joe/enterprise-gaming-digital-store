<?php

namespace App\Domains\Payments\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class GatewayNotConfigured extends ApplicationException
{
    public function __construct(string $message = 'This payment method is not available right now.')
    {
        parent::__construct($message, 'gateway_unavailable', 503);
    }
}
