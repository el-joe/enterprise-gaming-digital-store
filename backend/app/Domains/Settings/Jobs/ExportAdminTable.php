<?php

namespace App\Domains\Settings\Jobs;

use App\Domains\Settings\Services\AdminTableExporter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Writes one admin table export file (M14). Carries the export id only.
 */
class ExportAdminTable implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 300;

    public function __construct(public int $exportId) {}

    public function handle(AdminTableExporter $exporter): void
    {
        $exporter->run($this->exportId);
    }
}
