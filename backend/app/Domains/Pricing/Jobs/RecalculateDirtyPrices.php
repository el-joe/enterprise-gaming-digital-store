<?php

namespace App\Domains\Pricing\Jobs;

use App\Domains\Pricing\Services\VariantPriceRecalculator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateDirtyPrices implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(VariantPriceRecalculator $recalculator): void
    {
        $recalculator->run();
    }
}
