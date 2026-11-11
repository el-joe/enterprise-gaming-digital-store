<?php

namespace App\Domains\Currency\Enums;

/**
 * Where the display currency of a request came from (owner decision 1, OD-1). The first three are
 * an explicit choice and always win; `country` is the customer's country currency; `base` is the
 * fallback when nothing else gives a usable currency.
 */
enum DisplayCurrencySource: string
{
    case Header = 'header';
    case Cookie = 'cookie';
    case Profile = 'profile';
    case Country = 'country';
    case Base = 'base';

    public function isExplicit(): bool
    {
        return in_array($this, [self::Header, self::Cookie, self::Profile], true);
    }
}
