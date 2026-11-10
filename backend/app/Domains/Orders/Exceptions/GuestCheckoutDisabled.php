<?php

namespace App\Domains\Orders\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

/**
 * Web checkout requires a signed-in customer while `storefront.guest_checkout` is off
 * (owner decision 4, docs/OWNER_DECISIONS.md). 401, so a client treats it as "sign in first".
 */
class GuestCheckoutDisabled extends ApplicationException
{
    public function __construct()
    {
        parent::__construct('Please sign in or create an account to check out.', 'guest_checkout_disabled', 401);
    }
}
