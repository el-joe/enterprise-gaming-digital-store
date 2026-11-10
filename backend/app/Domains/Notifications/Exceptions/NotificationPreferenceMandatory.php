<?php

namespace App\Domains\Notifications\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

/**
 * A customer tried to switch off a transactional email that is always sent (M-A5).
 */
class NotificationPreferenceMandatory extends ApplicationException
{
    public function __construct(string $field)
    {
        parent::__construct('This notification cannot be turned off.', 'notification_preference_mandatory', 422, [$field => ['This notification cannot be turned off.']]);
    }
}
