<?php

namespace App\Domains\Payments\Jobs;

use App\Domains\Payments\Services\WebhookProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Processes one stored webhook (architecture 9.4 a to c). Carries the id only. A failure is
 * recorded on the row and retried by `payments:reconcile-open`.
 */
class ProcessPaymentWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public readonly int $webhookId)
    {
        $this->onQueue('webhooks');
    }

    public function handle(WebhookProcessor $processor): void
    {
        $processor->process($this->webhookId);
    }
}
