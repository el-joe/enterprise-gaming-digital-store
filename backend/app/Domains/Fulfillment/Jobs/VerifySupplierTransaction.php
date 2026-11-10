<?php

namespace App\Domains\Fulfillment\Jobs;

use App\Domains\Fulfillment\Services\SupplierVerificationService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class VerifySupplierTransaction implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $uniqueFor = 120;

    public function __construct(public readonly int $supplierTransactionId)
    {
        $this->onQueue('verification');
    }

    public function uniqueId(): string
    {
        return 'st:'.$this->supplierTransactionId;
    }

    public function handle(SupplierVerificationService $verifier): void
    {
        $verifier->verify($this->supplierTransactionId);
    }
}
