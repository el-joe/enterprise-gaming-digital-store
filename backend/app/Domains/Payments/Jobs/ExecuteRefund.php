<?php

namespace App\Domains\Payments\Jobs;

use App\Domains\Payments\Services\RefundExecutor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Sends one refund. Carries the id only; a repeated job is a no-op because only a `requested`
 * refund is sent. Lost jobs are recovered by `payments:reconcile-open`.
 */
class ExecuteRefund implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public readonly int $refundId)
    {
        $this->onQueue('payments');
    }

    public function handle(RefundExecutor $executor): void
    {
        $executor->execute($this->refundId);
    }
}
