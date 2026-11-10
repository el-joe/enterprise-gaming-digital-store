<?php

namespace App\Domains\Suppliers\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class SupplierNotConfigured extends ApplicationException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 'supplier_not_configured', 422);
    }
}
