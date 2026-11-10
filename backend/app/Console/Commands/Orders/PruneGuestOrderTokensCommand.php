<?php

namespace App\Console\Commands\Orders;

use App\Domains\Orders\Repositories\GuestOrderTokenRepository;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:prune-guest-order-tokens')]
#[Description('Delete expired guest order tokens of stateless clients')]
class PruneGuestOrderTokensCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(GuestOrderTokenRepository $tokens): int
    {
        $this->info('Pruned '.$tokens->pruneExpired().' guest order tokens.');

        return self::SUCCESS;
    }
}
