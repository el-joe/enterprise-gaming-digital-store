<?php

namespace App\Domains\Identity\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

/**
 * The device making the request cannot revoke itself through the device list; it signs out with
 * logout instead (M-A5).
 */
class CurrentDeviceRevocation extends ApplicationException
{
    public function __construct()
    {
        parent::__construct('This is the device you are using. Sign out instead.', 'current_device', 409);
    }
}
