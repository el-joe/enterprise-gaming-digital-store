<?php

namespace App\Domains\Suppliers\Enums;

/**
 * `full`: documented and implemented. `advisory`: usable as a hint only. `none`: not offered by
 * the supplier. `unverified`: documentation is incomplete, so the engine never relies on it.
 */
enum CapabilityLevel: string
{
    case Full = 'full';
    case Advisory = 'advisory';
    case None = 'none';
    case Unverified = 'unverified';

    public function isUsable(): bool
    {
        return $this === self::Full;
    }
}
