<?php

/**
 * M18 server-side GA4 `purchase` through the Measurement Protocol (INTEGRATIONS.md section 6),
 * from the queue row M10 writes once per order: sent exactly once, idempotent, retried, failed
 * after the attempt limit, and left pending while awaiting credentials. HTTP is `Http::fake`
 * only; the API secret never leaves the request URL.
 */

use App\Domains\Analytics\Enums\AnalyticsEventDispatchStatus;
use App\Domains\Analytics\Exceptions\MeasurementProtocolException;
use App\Domains\Analytics\Jobs\SendAnalyticsEvent;
use App\Domains\Analytics\Models\AnalyticsEventDispatch;
use App\Domains\Analytics\Services\AnalyticsEventDispatcher;
use App\Domains\Settings\Enums\SettingType;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

function purchaseRow(array $attributes = []): AnalyticsEventDispatch
{
    return AnalyticsEventDispatch::factory()->create([
        'dedupe_key' => 'purchase:BX-1001',
        'payload' => ['client_id' => '555.1700000000', 'transaction_id' => 'BX-1001', 'value' => '24.50', 'currency' => 'USD', 'items' => [['item_id' => 'STEAM-20-US', 'quantity' => 1]]],
        ...$attributes,
    ]);
}

beforeEach(function (): void {
    Http::preventStrayRequests();
});

it('sends purchase once with the documented body, then does nothing on any later run', function (): void {
    configureMeasurementProtocol('G-SHOP1234');
    Http::fake(['www.google-analytics.com/mp/collect*' => Http::response('', 204)]);
    $row = purchaseRow();

    expect(app(AnalyticsEventDispatcher::class)->dispatch($row->id))->toBe('sent')
        ->and(app(AnalyticsEventDispatcher::class)->dispatch($row->id))->toBe('skipped');
    SendAnalyticsEvent::dispatchSync($row->id);

    Http::assertSentCount(1);
    Http::assertSent(function (HttpRequest $request): bool {
        parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

        return str_starts_with($request->url(), 'https://www.google-analytics.com/mp/collect?')
            && $query === ['measurement_id' => 'G-SHOP1234', 'api_secret' => GA4_API_SECRET_CANARY]
            && $request['client_id'] === '555.1700000000'
            && $request['events'] === [['name' => 'purchase', 'params' => [
                'transaction_id' => 'BX-1001',
                'value' => 24.5,
                'currency' => 'USD',
                'items' => [['item_id' => 'STEAM-20-US', 'quantity' => 1]],
            ]]]
            && is_int($request['timestamp_micros'])
            && ! isset($request['consent']);
    });

    $row->refresh();
    expect($row->status)->toBe(AnalyticsEventDispatchStatus::Sent)
        ->and($row->attempts)->toBe(1)
        ->and($row->sent_at)->not->toBeNull();
});

it('uses the EU collection host when configured', function (): void {
    configureMeasurementProtocol();
    analyticsSetting('mp_region', 'eu');
    Http::fake(['region1.google-analytics.com/mp/collect*' => Http::response('', 204)]);

    expect(app(AnalyticsEventDispatcher::class)->dispatch(purchaseRow()->id))->toBe('sent');
    Http::assertSent(fn (HttpRequest $request): bool => str_starts_with($request->url(), 'https://region1.google-analytics.com/mp/collect?'));
});

it('sends a pseudonymous client id with ad consent denied when the browser gave none', function (): void {
    configureMeasurementProtocol();
    Http::fake(['www.google-analytics.com/*' => Http::response('', 204)]);
    $row = purchaseRow(['payload' => ['client_id' => null, 'transaction_id' => 'BX-1002', 'value' => '10.00', 'currency' => 'EGP', 'items' => []]]);

    app(AnalyticsEventDispatcher::class)->dispatch($row->id);

    Http::assertSent(fn (HttpRequest $request): bool => preg_match('/^\d+\.\d+$/', $request['client_id']) === 1
        && $request['consent'] === ['ad_user_data' => 'DENIED', 'ad_personalization' => 'DENIED']
        && $request['events'][0]['params']['currency'] === 'EGP');
});

it('stays pending without an attempt while awaiting credentials or switched off', function (): void {
    $row = purchaseRow();

    expect(app(AnalyticsEventDispatcher::class)->dispatch($row->id))->toBe('awaiting_credentials');

    configureMeasurementProtocol();
    analyticsSetting('server_purchase', false, SettingType::Bool);
    expect(app(AnalyticsEventDispatcher::class)->dispatch($row->id))->toBe('awaiting_credentials');

    Http::assertNothingSent();
    expect($row->refresh()->status)->toBe(AnalyticsEventDispatchStatus::Pending)->and($row->attempts)->toBe(0);

    $this->artisan('analytics:dispatch-pending')->expectsOutputToContain('awaiting credentials')->assertSuccessful();
});

it('counts a failed attempt for a retry and fails the row after the attempt limit', function (): void {
    configureMeasurementProtocol();
    Http::fake(['www.google-analytics.com/*' => Http::response('', 500)]);
    Log::spy();
    $row = purchaseRow();

    expect(fn () => app(AnalyticsEventDispatcher::class)->dispatch($row->id))->toThrow(MeasurementProtocolException::class);
    expect($row->refresh()->attempts)->toBe(1)->and($row->status)->toBe(AnalyticsEventDispatchStatus::Pending);

    $row->forceFill(['attempts' => AnalyticsEventDispatcher::MAX_ATTEMPTS - 1])->save();
    expect(app(AnalyticsEventDispatcher::class)->dispatch($row->id))->toBe('failed')
        ->and($row->refresh()->status)->toBe(AnalyticsEventDispatchStatus::Failed);

    // A failed row is never sent again.
    expect(app(AnalyticsEventDispatcher::class)->dispatch($row->id))->toBe('skipped');

    try {
        app(AnalyticsEventDispatcher::class)->dispatch(purchaseRow(['dedupe_key' => 'purchase:BX-2'])->id);
    } catch (MeasurementProtocolException $exception) {
        expect($exception->getMessage())->not->toContain(GA4_API_SECRET_CANARY);
    }
});

it('retries through the queued job with backoff', function (): void {
    $job = new SendAnalyticsEvent(1);

    expect($job->tries)->toBe(AnalyticsEventDispatcher::MAX_ATTEMPTS)
        ->and($job->backoff())->toBe([60, 300, 900, 3600]);
});

it('queues every pending row from the scheduler command', function (): void {
    configureMeasurementProtocol();
    Queue::fake([SendAnalyticsEvent::class]);
    $pending = purchaseRow();
    purchaseRow(['dedupe_key' => 'purchase:BX-SENT', 'status' => AnalyticsEventDispatchStatus::Sent]);

    $this->artisan('analytics:dispatch-pending')->expectsOutputToContain('Queued 1 analytics events.')->assertSuccessful();

    Queue::assertPushed(SendAnalyticsEvent::class, 1);
    Queue::assertPushed(SendAnalyticsEvent::class, fn (SendAnalyticsEvent $job): bool => $job->dispatchId === $pending->id);
});
