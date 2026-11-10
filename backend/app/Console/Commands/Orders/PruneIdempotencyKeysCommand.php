<?php

namespace App\Console\Commands\Orders;

use App\Domains\Orders\Services\IdempotencyStore;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:prune-idempotency-keys')]
#[Description('Delete idempotency keys past their retention')]
class PruneIdempotencyKeysCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(IdempotencyStore $store): int
    {
        $this->info('Pruned '.$store->prune().' keys.');

        return self::SUCCESS;
    }
}
