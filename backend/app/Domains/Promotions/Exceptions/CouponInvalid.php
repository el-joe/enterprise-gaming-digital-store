<?php

namespace App\Domains\Promotions\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class CouponInvalid extends ApplicationException
{
    public function __construct(string $message = 'This coupon cannot be used.')
    {
        parent::__construct($message, 'coupon_invalid', 422, ['code' => [$message]]);
    }
}
