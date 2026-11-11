<?php

namespace App\Domains\Notifications\Enums;

enum PushDeliveryOutcome: string
{
    /** FCM accepted the message. */
    case Sent = 'sent';

    /** The token is unregistered, invalid or belongs to another Firebase project: delete it. */
    case TokenInvalid = 'token_invalid';

    /** Quota, server error, network failure or an expired access token: try again later. */
    case Retry = 'retry';

    /** Refused for another reason (a bad message or configuration): do not retry. */
    case Failed = 'failed';
}
