<?php

namespace App\Domains\Pricing\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class MarginGuardRefused extends ApplicationException
{
    /**
     * @param  array<string, list<string>>  $errors
     */
    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message, 'below_margin', 422, $errors);
    }
}
