<?php

namespace App\Domains\Notifications\Enums;

/**
 * `sms` (M17) is the secondary channel used by the n8n customer workflows. It is opt-in: without
 * an enabled preference row for the category, no transactional SMS is sent.
 *
 * `push` (M-A18, ADR-042) goes to the customer's signed-in app devices through Firebase Cloud
 * Messaging. It exists for the transactional categories only (orders and support) and is on by
 * default there; see `NotificationPreferenceService::isAvailable()`.
 */
enum NotificationPreferenceChannel: string
{
    case Mail = 'mail';
    case Database = 'database';
    case Sms = 'sms';
    case Push = 'push';
}
