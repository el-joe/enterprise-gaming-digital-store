<?php

namespace App\Console\Commands\Fulfillment;

use App\Domains\Fulfillment\Exceptions\DigitalCodeKeyChangeRefused;
use App\Domains\Fulfillment\Services\DigitalCodeKeyManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('codes:rebuild-blind-index
    {--force : Do not ask for confirmation}
    {--chunk=200 : Codes re-indexed per batch}')]
#[Description('Replace the code blind index key and recompute every code hash (the only way to change it)')]
class RebuildBlindIndexCommand extends Command
{
    public function handle(DigitalCodeKeyManager $keys): int
    {
        $this->warn('This replaces the blind index key and recomputes the duplicate-check hash of every code.');
        $this->warn('Put the store in maintenance mode and stop the queue workers first: a code imported while it runs may miss the duplicate check.');

        if (! $this->option('force') && ! $this->confirm('Rebuild the blind index now?')) {
            $this->line('Nothing changed.');

            return self::FAILURE;
        }

        try {
            $count = $keys->rebuildBlindIndex(max(1, (int) $this->option('chunk')));
        } catch (DigitalCodeKeyChangeRefused $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Blind index rebuilt: {$count} codes re-indexed with the new key. Audited.");

        return self::SUCCESS;
    }
}
