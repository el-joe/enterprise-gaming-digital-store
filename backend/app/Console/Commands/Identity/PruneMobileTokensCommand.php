<?php

namespace App\Console\Commands\Identity;

use App\Domains\Identity\Repositories\MobileTokenRepository;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('auth:prune-mobile-tokens')]
#[Description('Delete expired mobile refresh tokens, two-factor login challenges and customer access tokens')]
class PruneMobileTokensCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(MobileTokenRepository $tokens): int
    {
        $pruned = $tokens->pruneExpired();

        $this->info("Pruned {$pruned['refresh_tokens']} refresh tokens, {$pruned['challenges']} login challenges and {$pruned['access_tokens']} access tokens.");

        return self::SUCCESS;
    }
}
