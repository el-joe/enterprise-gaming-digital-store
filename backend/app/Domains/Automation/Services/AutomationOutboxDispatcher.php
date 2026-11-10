<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Enums\AutomationOutboxStatus;
use App\Domains\Automation\Models\AutomationOutbox;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;

/**
 * `automation:flush-outbox`: delivers pending events to the n8n router webhook (Z03).
 *
 * Due events are leased in a short transaction with SKIP LOCKED (so two runners never send the
 * same event at once), then sent outside any transaction. Each request carries the header-auth
 * token, `X-Signature-Timestamp` and `X-Signature` = hex HMAC-SHA256 of `timestamp.body` with
 * N8N_OUTBOUND_SECRET. A 2xx marks the event delivered. Anything else waits the next retry delay;
 * after the last delay the event is marked failed with the HTTP status or a short reason, never
 * the response body. n8n deduplicates by `event_id`, so a redelivery after a lost response is safe.
 */
class AutomationOutboxDispatcher
{
    public function __construct(private AutomationSignature $signature) {}

    public function isConfigured(): bool
    {
        return config('automation.outbox.webhook_url') !== '' && config('automation.outbox.secret') !== '';
    }

    /**
     * @return array{configured: bool, leased: int, delivered: int, retrying: int, failed: int}
     */
    public function flush(?int $limit = null): array
    {
        $result = ['configured' => $this->isConfigured(), 'leased' => 0, 'delivered' => 0, 'retrying' => 0, 'failed' => 0];

        if (! $result['configured']) {
            return $result;
        }

        $events = $this->lease($limit ?? (int) config('automation.outbox.batch_size'));
        $result['leased'] = $events->count();

        foreach ($events as $event) {
            $outcome = $this->deliver($event);
            $result[$outcome]++;
        }

        return $result;
    }

    /**
     * @return Collection<int, AutomationOutbox>
     */
    private function lease(int $limit): Collection
    {
        return DB::transaction(function () use ($limit): Collection {
            $events = AutomationOutbox::query()
                ->where('status', AutomationOutboxStatus::Pending)
                ->where(static fn ($due) => $due->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now()))
                ->orderBy('id')
                ->limit($limit)
                ->lock('FOR UPDATE SKIP LOCKED')
                ->get();

            if ($events->isNotEmpty()) {
                AutomationOutbox::query()->whereKey($events->modelKeys())->update(['next_attempt_at' => now()->addSeconds((int) config('automation.outbox.lease_seconds'))]);
            }

            return $events;
        });
    }

    /**
     * @return 'delivered'|'retrying'|'failed'
     */
    private function deliver(AutomationOutbox $event): string
    {
        $timestamp = (string) now()->getTimestamp();

        try {
            $body = json_encode([
                'event_id' => $event->event_id,
                'event_type' => $event->event_type,
                'occurred_at' => $event->created_at?->toIso8601String(),
                'attempt' => $event->attempts + 1,
                'payload' => $event->payload,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $this->failed($event, null, 'payload could not be encoded');
        }

        $headers = [
            'X-Signature-Timestamp' => $timestamp,
            'X-Signature' => $this->signature->signOutbound($timestamp, $body, (string) config('automation.outbox.secret')),
            'X-Event-Id' => $event->event_id,
            'X-Event-Type' => $event->event_type,
        ];

        if (config('automation.outbox.auth_token') !== '') {
            $headers[(string) config('automation.outbox.auth_header')] = (string) config('automation.outbox.auth_token');
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout((int) config('automation.outbox.timeout_seconds'))
                ->connectTimeout(5)
                ->withBody($body, 'application/json')
                ->post((string) config('automation.outbox.webhook_url'));
        } catch (ConnectionException) {
            return $this->retryOrFail($event, null, 'connection failed or timed out');
        }

        if ($response->successful()) {
            $event->forceFill([
                'status' => AutomationOutboxStatus::Delivered,
                'attempts' => $event->attempts + 1,
                'delivered_at' => now(),
                'next_attempt_at' => null,
                'last_http_status' => $response->status(),
                'last_error' => null,
            ])->save();

            return 'delivered';
        }

        return $this->retryOrFail($event, $response, 'HTTP '.$response->status());
    }

    /**
     * @return 'retrying'|'failed'
     */
    private function retryOrFail(AutomationOutbox $event, ?Response $response, string $reason): string
    {
        /** @var list<int> $delays */
        $delays = config('automation.outbox.retry_delays_seconds');
        $attempts = $event->attempts + 1;

        if ($attempts > count($delays)) {
            return $this->failed($event, $response, $reason);
        }

        $event->forceFill([
            'attempts' => $attempts,
            'next_attempt_at' => now()->addSeconds($delays[$attempts - 1]),
            'last_http_status' => $response?->status(),
            'last_error' => mb_substr($reason, 0, 500),
        ])->save();

        return 'retrying';
    }

    /**
     * @return 'failed'
     */
    private function failed(AutomationOutbox $event, ?Response $response, string $reason): string
    {
        $event->forceFill([
            'status' => AutomationOutboxStatus::Failed,
            'attempts' => $event->attempts + 1,
            'next_attempt_at' => null,
            'failed_at' => now(),
            'last_http_status' => $response?->status(),
            'last_error' => mb_substr($reason, 0, 500),
        ])->save();

        Log::warning('automation.outbox_delivery_failed', ['event_id' => $event->event_id, 'event_type' => $event->event_type, 'reason' => $reason]);

        return 'failed';
    }
}
