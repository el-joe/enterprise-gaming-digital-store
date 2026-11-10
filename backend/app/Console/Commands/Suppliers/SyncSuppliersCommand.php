<?php

namespace App\Console\Commands\Suppliers;

use App\Domains\Suppliers\Enums\SupplierStatus;
use App\Domains\Suppliers\Jobs\SyncSupplierCatalog;
use App\Domains\Suppliers\Models\Supplier;
use Illuminate\Console\Command;

/**
 * Queues a catalog or pricing sync for active suppliers, once per window (uq_ssr_window).
 */
abstract class SyncSuppliersCommand extends Command
{
    public function handle(): int
    {
        $type = $this->type();

        $window = $type === 'catalog' ? now()->format('Y-m-d') : now()->format('Y-m-d').'-'.intdiv((int) now()->format('G'), 6);

        Supplier::query()->where('status', SupplierStatus::Active)
            ->when($this->option('supplier'), static fn ($query, $id) => $query->whereKey($id))
            ->pluck('id')
            ->each(static fn (int $id) => SyncSupplierCatalog::dispatch($id, $type, 'scheduler', $window));

        return self::SUCCESS;
    }

    abstract protected function type(): string;
}
