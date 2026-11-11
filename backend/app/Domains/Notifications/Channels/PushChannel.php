<?php

namespace App\Domains\Notifications\Channels;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Contracts\PushNotification;
use App\Domains\Notifications\Jobs\SendPushMessage;
use App\Domains\Notifications\Repositories\PushTokenRepository;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * The `push` notification channel (M-A18, ADR-042), registered with `Notification::extend('push')`.
 * Notifications list it in `via()` only when the customer's `push` preference for the category is
 * on (`NotificationPreferenceService::channelsFor`). Accounts only; a guest has no device.
 *
 * With `push.enabled` false it is a no-op that logs at debug level. Otherwise one queued
 * {@see SendPushMessage} per signed-in device with a token.
 */
class PushChannel
{
    public function __construct(private PushTokenRepository $tokens) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notifiable instanceof User || ! $notification instanceof PushNotification) {
            return;
        }

        if (! config('push.enabled')) {
            Log::debug('push.disabled', ['notification' => $notification::class, 'user_id' => $notifiable->id]);

            return;
        }

        $message = $notification->toPush($notifiable);

        if ($message === null) {
            return;
        }

        foreach ($this->tokens->liveIdsForUser($notifiable->id) as $pushTokenId) {
            SendPushMessage::dispatch($pushTokenId, $message->toArray());
        }
    }
}
