<?php

namespace App\Domains\Orders\Enums;

/**
 * How a guest order token was obtained (ADR-036). `Placement` and `Otp` open the order to the
 * client for status and payment calls, like the browser session's order list; `Link` only
 * records that the client opened the signed delivery link.
 */
enum GuestOrderTokenSource: string
{
    case Placement = 'placement';
    case Otp = 'otp';
    case Link = 'link';

    public function opensOrder(): bool
    {
        return $this !== self::Link;
    }
}
