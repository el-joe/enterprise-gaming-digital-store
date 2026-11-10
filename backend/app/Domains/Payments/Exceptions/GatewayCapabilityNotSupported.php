<?php

namespace App\Domains\Payments\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class GatewayCapabilityNotSupported extends ApplicationException
{
    public function __construct(string $gateway, string $operation)
    {
        parent::__construct("{$gateway} does not support {$operation}.", 'capability_not_supported', 422);
    }
}
