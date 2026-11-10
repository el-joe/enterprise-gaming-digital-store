<?php

namespace App\Console\Commands\Pricing;

use App\Domains\Pricing\Jobs\RecalculateDirtyPrices;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('pricing:recalculate-dirty')]
#[Description('Queue recalculation of listing prices for variants marked dirty')]
class RecalculateDirtyCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        RecalculateDirtyPrices::dispatch();

        return self::SUCCESS;
    }
}
