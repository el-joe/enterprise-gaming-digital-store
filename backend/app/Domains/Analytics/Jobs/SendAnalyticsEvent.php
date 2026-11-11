<?php

namespace App\Domains\Analytics\Jobs;

use App\Domains\Analytics\Services\AnalyticsEventDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Send one server-side analytics event (GA4 `purchase`) through the Measurement Protocol
 * (M18). Retried with backoff; the dispatcher makes every run after the first success a no-op.
 * Carries the row id only, never the payload.
 */
class SendAnalyticsEvent implements ShouldQueue
{
    use Queueable;

    public int $tries = AnalyticsEventDispatcher::MAX_ATTEMPTS;

    public function __construct(public int $dispatchId) {}

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(AnalyticsEventDispatcher $dispatcher): void
    {
        $dispatcher->dispatch($this->dispatchId);
    }
}
