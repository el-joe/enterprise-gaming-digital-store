<?php

namespace App\Domains\Shared\Exceptions;

use RuntimeException;

/**
 * Expected domain failure, rendered with its own code in the JSON error envelope.
 */
class ApplicationException extends RuntimeException
{
    /**
     * @param  array<string, list<string>>  $errors
     */
    public function __construct(
        string $message,
        public readonly string $errorCode,
        public readonly int $status = 422,
        public readonly array $errors = [],
    ) {
        parent::__construct($message);
    }
}
