<?php

namespace App\Console\Commands\Analytics;

use App\Domains\Analytics\Enums\AnalyticsEventDispatchStatus;
use App\Domains\Analytics\Jobs\SendAnalyticsEvent;
use App\Domains\Analytics\Models\AnalyticsEventDispatch;
use App\Domains\Analytics\Services\AnalyticsEventDispatcher;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('analytics:dispatch-pending {--limit=500 : Rows queued per run}')]
#[Description('Queue the pending GA4 Measurement Protocol events (server-side purchase)')]
class DispatchPendingEventsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AnalyticsEventDispatcher $dispatcher): int
    {
        if (! $dispatcher->ready()) {
            $this->info('GA4 Measurement Protocol not configured (awaiting credentials) or server purchases off; nothing queued.');

            return self::SUCCESS;
        }

        $ids = AnalyticsEventDispatch::query()
            ->where('status', AnalyticsEventDispatchStatus::Pending)
            ->orderBy('id')
            ->limit(max(1, (int) $this->option('limit')))
            ->pluck('id');

        $ids->each(static fn (int $id) => SendAnalyticsEvent::dispatch($id));

        $this->info('Queued '.$ids->count().' analytics events.');

        return self::SUCCESS;
    }
}
