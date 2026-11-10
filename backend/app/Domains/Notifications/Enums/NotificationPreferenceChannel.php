<?php

namespace App\Domains\Notifications\Enums;

/**
 * `sms` (M17) is the secondary channel used by the n8n customer workflows. It is opt-in: without
 * an enabled preference row for the category, no transactional SMS is sent.
 */
enum NotificationPreferenceChannel: string
{
    case Mail = 'mail';
    case Database = 'database';
    case Sms = 'sms';
}
