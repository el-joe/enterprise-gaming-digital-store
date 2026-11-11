<?php

namespace App\Domains\Notifications\Jobs;

use App\Domains\Notifications\DTOs\PushMessage;
use App\Domains\Notifications\Enums\PushDeliveryOutcome;
use App\Domains\Notifications\Exceptions\PushNotConfigured;
use App\Domains\Notifications\Models\MobilePushToken;
use App\Domains\Notifications\Services\FcmClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Delivers one push message to one device (M-A18, ADR-042). Carries the token row id and the
 * message (text and ids, no secret); the FCM token itself is read from the database when the job
 * runs, so it never sits in the queue. A token deleted meanwhile (sign-out) sends nothing.
 *
 * Retries `push.tries` times with the `push.backoff` waits (or FCM's Retry-After) on quota, server
 * and network errors; an unregistered or invalid token is deleted at once; a configuration error
 * fails the job without retrying.
 */
class SendPushMessage implements ShouldQueue
{
    use Queueable;

    public int $tries;

    /**
     * @param  array{type: string, title: string, body: string, data: array<string, string>}  $message
     */
    public function __construct(public readonly int $pushTokenId, public readonly array $message)
    {
        $this->tries = max(1, (int) config('push.tries'));
        $this->onQueue('notifications');
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return array_values(array_map(intval(...), (array) config('push.backoff')));
    }

    public function handle(FcmClient $fcm): void
    {
        $message = PushMessage::fromArray($this->message);

        if (! config('push.enabled')) {
            Log::debug('push.disabled', ['type' => $message->type, 'push_token_id' => $this->pushTokenId]);

            return;
        }

        $device = MobilePushToken::query()->find($this->pushTokenId);

        if ($device === null) {
            return;
        }

        try {
            $result = $fcm->send($device->token, $message);
        } catch (PushNotConfigured $exception) {
            Log::error('push.not_configured', ['reason' => $exception->getMessage()]);
            $this->fail($exception);

            return;
        }

        match ($result->outcome) {
            PushDeliveryOutcome::Sent => $device->forceFill(['last_sent_at' => now()])->save(),
            PushDeliveryOutcome::TokenInvalid => $this->forget($device, (string) $result->error),
            PushDeliveryOutcome::Retry => $this->release($result->retryAfterSeconds ?? $this->nextWait()),
            PushDeliveryOutcome::Failed => Log::warning('push.not_delivered', ['type' => $message->type, 'push_token_id' => $device->id, 'error' => $result->error]),
        };
    }

    public function failed(?Throwable $exception): void
    {
        Log::warning('push.gave_up', ['push_token_id' => $this->pushTokenId, 'type' => $this->message['type'] ?? null]);
    }

    private function forget(MobilePushToken $device, string $error): void
    {
        $device->delete();
        Log::info('push.token_removed', ['push_token_id' => $device->id, 'user_id' => $device->user_id, 'error' => $error]);
    }

    private function nextWait(): int
    {
        $waits = $this->backoff();

        return $waits === [] ? 60 : $waits[min(max(0, $this->attempts() - 1), count($waits) - 1)];
    }
}
