<?php

namespace App\Domains\Suppliers\Exceptions;

use App\Domains\Suppliers\Enums\SupplierCapability;
use LogicException;

/**
 * An operation the adapter declares unsupported was called. Callers must check capabilities()
 * first, so reaching this is a bug and is logged at error level by whoever catches it.
 */
class CapabilityNotSupported extends LogicException
{
    public function __construct(public readonly string $adapterKey, public readonly SupplierCapability $capability)
    {
        parent::__construct("Supplier adapter {$adapterKey} does not support {$capability->value}.");
    }
}
