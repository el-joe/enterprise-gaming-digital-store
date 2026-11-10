<?php

namespace App\Domains\Payments\Exceptions;

use RuntimeException;

/**
 * A provider call failed. `ambiguous` is true when the request may have been executed (read
 * timeout, reset, 5xx): the outcome must be re-read, never assumed. The message names the
 * operation and status only, never a body or a credential.
 */
class GatewayRequestFailed extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly bool $ambiguous,
        public readonly ?int $httpStatus = null,
        public readonly ?string $providerCode = null,
    ) {
        parent::__construct($message);
    }
}
