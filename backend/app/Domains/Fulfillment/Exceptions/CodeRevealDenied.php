<?php

namespace App\Domains\Fulfillment\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class CodeRevealDenied extends ApplicationException
{
    public function __construct(public readonly string $reason, string $message = 'This code cannot be revealed.', int $status = 403)
    {
        parent::__construct($message, match ($reason) {
            'recent_auth_required' => 'recent_auth_required',
            'not_found', 'not_owner' => 'not_found',
            default => 'reveal_denied',
        }, $status);
    }
}
