<?php

namespace App\Domains\Orders\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class GuestOtpInvalid extends ApplicationException
{
    public function __construct()
    {
        parent::__construct('The code is invalid or has expired.', 'otp_invalid', 422, ['code' => ['The code is invalid or has expired.']]);
    }
}
