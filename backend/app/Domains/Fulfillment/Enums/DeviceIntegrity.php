<?php

namespace App\Domains\Fulfillment\Enums;

/**
 * The app's own device-integrity verdict sent with a code reveal (`X-Device-Integrity`, owner
 * decision 5, OD-1). Advisory only: it is stored in the reveal audit for the risk engine and
 * never blocks a reveal. Client-reported, so it can be forged; it is a signal, not a proof.
 */
enum DeviceIntegrity: string
{
    case Ok = 'ok';
    case Compromised = 'compromised';
    case Unknown = 'unknown';

    public const string HEADER = 'X-Device-Integrity';

    /**
     * Null when the header is absent; any value outside the three known ones is stored as
     * `unknown`, so a malformed header is still recorded and never refused.
     */
    public static function fromHeader(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom(strtolower(trim($value))) ?? self::Unknown;
    }
}
