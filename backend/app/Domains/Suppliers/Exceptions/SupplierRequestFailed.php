<?php

namespace App\Domains\Suppliers\Exceptions;

use RuntimeException;

/**
 * A read call to a supplier failed. The message names the operation and status only, never the
 * response body or the request URL with credentials.
 */
class SupplierRequestFailed extends RuntimeException
{
    public function __construct(string $message, public readonly ?int $httpStatus = null)
    {
        parent::__construct($message);
    }
}
