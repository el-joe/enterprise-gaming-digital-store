<?php

namespace App\Domains\Shared\Services;

use Illuminate\Log\Logger;
use Monolog\Logger as Monolog;

/**
 * Logging channel "tap" that attaches the redaction processor.
 */
class ApplyLogRedaction
{
    public function __construct(private RedactSensitiveLogData $processor) {}

    public function __invoke(Logger $logger): void
    {
        $monolog = $logger->getLogger();

        if ($monolog instanceof Monolog) {
            $monolog->pushProcessor($this->processor);
        }
    }
}
