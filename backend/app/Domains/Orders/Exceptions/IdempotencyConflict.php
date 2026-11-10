<?php

namespace App\Domains\Orders\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class IdempotencyConflict extends ApplicationException
{
    public static function mismatch(): self
    {
        return new self('This Idempotency-Key was already used with a different request.', 'idempotency_mismatch', 409);
    }

    public static function inProgress(): self
    {
        return new self('A request with this Idempotency-Key is still being processed.', 'idempotency_in_progress', 409);
    }
}
