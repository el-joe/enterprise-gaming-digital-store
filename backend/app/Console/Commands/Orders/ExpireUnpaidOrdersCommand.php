<?php

namespace App\Console\Commands\Orders;

use App\Domains\Orders\Services\OrderExpiryService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:expire-unpaid {--limit=500 : Most orders to handle in one run}')]
#[Description('Cancel unpaid orders whose payment window has expired')]
class ExpireUnpaidOrdersCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(OrderExpiryService $expiry): int
    {
        $result = $expiry->expireDue((int) $this->option('limit'));

        $this->info("Cancelled {$result['cancelled']}, skipped {$result['skipped']}.");

        return self::SUCCESS;
    }
}
