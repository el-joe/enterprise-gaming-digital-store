<?php

namespace App\Domains\Payments\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class RefundRefused extends ApplicationException
{
    public function __construct(string $message, string $code = 'refund_refused', int $status = 422)
    {
        parent::__construct($message, $code, $status, ['refund' => [$message]]);
    }
}
