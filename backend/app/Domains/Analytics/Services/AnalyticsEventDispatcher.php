<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Enums\AnalyticsEventDispatchStatus;
use App\Domains\Analytics\Exceptions\MeasurementProtocolException;
use App\Domains\Analytics\Models\AnalyticsEventDispatch;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Sends a queued server-side event (`analytics_event_dispatches`, written once per order by
 * RecordPurchaseEvent) to the GA4 Measurement Protocol, at most once (M18):
 *
 * - Nothing happens while the Measurement Protocol is not configured or server purchases are
 *   switched off: the row stays `pending` without spending an attempt ("awaiting credentials").
 * - A lock per row and the `sent` status make a second worker or a retry a no-op; GA4 also
 *   deduplicates purchases by `transaction_id` (the order number).
 * - A failure counts an attempt and is retried by the job; after `MAX_ATTEMPTS` the row is
 *   `failed` and stays visible for an operator.
 *
 * The body holds no personal data: the GA client id the browser sent with consent, else a
 * pseudonymous id derived from the dedupe key with ad consent denied.
 */
class AnalyticsEventDispatcher
{
    public const int MAX_ATTEMPTS = 5;

    private const int BACKDATE_LIMIT_HOURS = 72;

    public function __construct(private AnalyticsSettings $settings, private MeasurementProtocolClient $client) {}

    public function ready(): bool
    {
        return $this->settings->serverPurchaseEnabled() && $this->settings->measurementProtocolConfigured();
    }

    /**
     * @return 'sent'|'skipped'|'awaiting_credentials'|'failed'
     *
     * @throws MeasurementProtocolException when the attempt failed and may be retried
     */
    public function dispatch(int $dispatchId): string
    {
        if (! $this->ready()) {
            return 'awaiting_credentials';
        }

        return Cache::lock('analytics:dispatch:'.$dispatchId, 60)->block(10, function () use ($dispatchId): string {
            $row = AnalyticsEventDispatch::query()->find($dispatchId);

            if ($row === null || $row->status !== AnalyticsEventDispatchStatus::Pending) {
                return 'skipped';
            }

            try {
                $this->client->send($this->body($row));
            } catch (MeasurementProtocolException $exception) {
                $attempts = $row->attempts + 1;

                DB::table('analytics_event_dispatches')->where('id', $row->id)->update([
                    'attempts' => $attempts,
                    'status' => $attempts >= self::MAX_ATTEMPTS ? AnalyticsEventDispatchStatus::Failed->value : AnalyticsEventDispatchStatus::Pending->value,
                    'updated_at' => now(),
                ]);

                if ($attempts >= self::MAX_ATTEMPTS) {
                    return 'failed';
                }

                throw $exception;
            }

            DB::table('analytics_event_dispatches')->where('id', $row->id)->update([
                'attempts' => $row->attempts + 1,
                'status' => AnalyticsEventDispatchStatus::Sent->value,
                'sent_at' => now(),
                'updated_at' => now(),
            ]);

            return 'sent';
        });
    }

    /**
     * The Measurement Protocol request body for a stored row.
     *
     * @return array<string, mixed>
     */
    public function body(AnalyticsEventDispatch $row): array
    {
        $payload = (array) $row->payload;
        $clientId = is_string($payload['client_id'] ?? null) && $payload['client_id'] !== '' ? $payload['client_id'] : null;

        $params = [
            'transaction_id' => (string) $payload['transaction_id'],
            'value' => (float) $payload['value'],
            'currency' => (string) $payload['currency'],
            'items' => array_map(static fn (array $item): array => [
                'item_id' => mb_substr((string) $item['item_id'], 0, 100),
                'quantity' => (int) $item['quantity'],
            ], array_slice((array) ($payload['items'] ?? []), 0, 200)),
        ];

        $body = [
            'client_id' => $clientId ?? $this->pseudonymousClientId($row),
            'events' => [['name' => $row->event_name->value, 'params' => $params]],
        ];

        if ($row->created_at !== null && $row->created_at->greaterThan(now()->subHours(self::BACKDATE_LIMIT_HOURS))) {
            $body['timestamp_micros'] = $row->created_at->getTimestamp() * 1_000_000;
        }

        if ($clientId === null) {
            $body['consent'] = ['ad_user_data' => 'DENIED', 'ad_personalization' => 'DENIED'];
        }

        return $body;
    }

    private function pseudonymousClientId(AnalyticsEventDispatch $row): string
    {
        $hash = hash_hmac('sha256', $row->dedupe_key, (string) config('app.key'));

        return hexdec(substr($hash, 0, 8)).'.'.($row->created_at?->getTimestamp() ?? 0);
    }
}
