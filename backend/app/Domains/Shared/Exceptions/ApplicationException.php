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

    /**
     * The message in the request locale (M-A3). By default the English message is the JSON
     * translation key; exceptions with variable parts override this with a placeholder key.
     */
    public function localizedMessage(): string
    {
        return __($this->getMessage());
    }

    /**
     * Field errors in the request locale, translated like the message.
     *
     * @return array<string, list<string>>
     */
    public function localizedErrors(): array
    {
        return array_map(static fn (array $messages): array => array_map(static fn (string $message): string => __($message), $messages), $this->errors);
    }
}
