<?php

namespace App\Console\Commands\Settings;

use App\Domains\Settings\Services\AdminTableExporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('admin:prune-exports')]
#[Description('Delete the files of expired admin table exports')]
class PruneExportsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AdminTableExporter $exports): int
    {
        $this->info('Removed '.$exports->pruneExpired().' expired export files.');

        return self::SUCCESS;
    }
}
