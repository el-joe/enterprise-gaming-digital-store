<?php

namespace App\Domains\Fulfillment\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

/**
 * A code with the same blind index already exists. The message never includes the code.
 */
class DuplicateDigitalCode extends ApplicationException
{
    public function __construct()
    {
        parent::__construct('This code already exists.', 'duplicate_code', 409);
    }
}
