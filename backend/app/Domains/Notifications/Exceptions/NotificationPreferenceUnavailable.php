<?php

namespace App\Domains\Notifications\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

/**
 * A customer sent a preference cell the category does not have, such as push for marketing
 * (M-A18).
 */
class NotificationPreferenceUnavailable extends ApplicationException
{
    public function __construct(string $field)
    {
        parent::__construct('This channel is not available for this kind of notification.', 'notification_preference_unavailable', 422, [$field => ['This channel is not available for this kind of notification.']]);
    }
}
