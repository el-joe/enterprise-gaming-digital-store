<?php

namespace App\Domains\Suppliers\Jobs;

use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Services\SupplierCatalogSync;
use App\Domains\Suppliers\Services\SupplierPriceSync;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncSupplierCatalog implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 600;

    public function __construct(public readonly int $supplierId, public readonly string $type, public readonly string $triggeredBy, public readonly ?string $windowKey = null)
    {
        $this->onQueue('sync');
    }

    public function handle(SupplierCatalogSync $catalog, SupplierPriceSync $pricing): void
    {
        $supplier = Supplier::query()->find($this->supplierId);

        if ($supplier === null) {
            return;
        }

        $this->type === 'pricing'
            ? $pricing->run($supplier, $this->triggeredBy, $this->windowKey)
            : $catalog->run($supplier, $this->triggeredBy, $this->windowKey);
    }
}
