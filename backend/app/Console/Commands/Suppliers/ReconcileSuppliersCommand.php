<?php

namespace App\Console\Commands\Suppliers;

use App\Domains\Suppliers\Enums\SupplierStatus;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Services\SupplierReconciler;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('suppliers:reconcile-daily')]
#[Description('Compare supplier transaction reports with our attempts for the last 48 hours')]
class ReconcileSuppliersCommand extends Command
{
    public function handle(SupplierReconciler $reconciler): int
    {
        $windowEnd = CarbonImmutable::now()->startOfHour();

        foreach (Supplier::query()->where('status', SupplierStatus::Active)->get() as $supplier) {
            $run = $reconciler->run($supplier, $windowEnd);
            $this->line("{$supplier->name}: ".($run === null ? 'skipped' : "{$run->status->value}, {$run->mismatches} mismatches"));
        }

        return self::SUCCESS;
    }
}
