<?php

namespace App\Domains\Identity\Enums;

/**
 * A signed-in device of a customer (M-A5): a mobile sign-in (refresh-token family) or a browser
 * session (listed only with the `database` session driver).
 */
enum AccountDeviceType: string
{
    case Mobile = 'mobile';
    case Web = 'web';
}
