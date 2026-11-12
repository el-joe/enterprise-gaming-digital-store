<?php

namespace App\Domains\Suppliers\Jobs;

use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Services\SupplierImageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;

/**
 * Downloads and optimizes one supplier product's image (owner decision 12) on the `sync` queue,
 * so the catalog sync itself only queues it. Refusals are stored on the row; a network failure is
 * retried with backoff and then left for the next sync or `suppliers:sync-images`. A duplicate job
 * is harmless: an unchanged URL is not downloaded again.
 */
class FetchSupplierProductImage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(public readonly int $supplierProductId, public readonly bool $force = false)
    {
        $this->onQueue('sync');
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(SupplierImageService $images): void
    {
        $source = SupplierProduct::query()->find($this->supplierProductId);

        if ($source === null) {
            return;
        }

        try {
            $images->refresh($source, $this->force);
        } catch (ConnectionException $exception) {
            $source->forceFill(['image_error' => 'network'])->save();

            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff()[$this->attempts() - 1] ?? 300);
            }
        }
    }
}
