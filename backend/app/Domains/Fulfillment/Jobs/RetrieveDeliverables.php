<?php

namespace App\Domains\Fulfillment\Jobs;

use App\Domains\Fulfillment\Services\DeliverableRetriever;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Fetches a purchased code. A failure is recorded and the sweeper dispatches the GET again later;
 * this job never submits an order. Carries the attempt id only, never code material.
 */
class RetrieveDeliverables implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public readonly int $supplierTransactionId)
    {
        $this->onQueue('verification');
    }

    public function handle(DeliverableRetriever $retriever): void
    {
        $retriever->retrieve($this->supplierTransactionId);
    }
}
