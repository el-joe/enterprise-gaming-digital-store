<?php

namespace App\Console\Commands\Suppliers;

use App\Domains\Fulfillment\Services\StaleSubmissionSweeper;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('suppliers:sweep-stale-submissions')]
#[Description('Move interrupted supplier submissions to pending verification and re-dispatch stuck units')]
class SweepStaleSubmissionsCommand extends Command
{
    public function handle(StaleSubmissionSweeper $sweeper): int
    {
        $result = $sweeper->sweep();

        $this->info("Stale {$result['stale_submissions']}, requeued {$result['requeued_units']}, deliverable retries {$result['deliverable_retries']}.");

        return self::SUCCESS;
    }
}
