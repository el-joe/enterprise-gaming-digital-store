<?php

namespace App\Console\Commands\Suppliers;

use App\Domains\Suppliers\Enums\SupplierStatus;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Services\SupplierMonitor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('suppliers:refresh-balances')]
#[Description('Refresh the cached balance of every active supplier that reports one')]
class RefreshBalancesCommand extends Command
{
    public function handle(SupplierMonitor $monitor): int
    {
        Supplier::query()->where('status', SupplierStatus::Active)->get()->each(static fn (Supplier $supplier) => $monitor->refreshBalance($supplier));

        return self::SUCCESS;
    }
}
