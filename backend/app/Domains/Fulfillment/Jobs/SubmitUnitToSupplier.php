<?php

namespace App\Domains\Fulfillment\Jobs;

use App\Domains\Fulfillment\Services\SupplierSubmissionGate;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Submits one unit through the exactly-once gate. `tries = 1`: a failure after the attempt row
 * is committed is resolved by the sweeper and verification, never by a queue retry. The unique
 * key only reduces duplicate dispatches; the database gate is the safety mechanism.
 */
class SubmitUnitToSupplier implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $uniqueFor = 300;

    public function __construct(public readonly int $unitId)
    {
        $this->onQueue('fulfillment');
    }

    public function uniqueId(): string
    {
        return 'unit:'.$this->unitId;
    }

    public function handle(SupplierSubmissionGate $gate): void
    {
        $gate->submit($this->unitId);
    }
}
