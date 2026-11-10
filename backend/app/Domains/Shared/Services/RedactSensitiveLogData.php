<?php

namespace App\Domains\Shared\Services;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Monolog processor removing sensitive keys from a record's context and extra data.
 */
class RedactSensitiveLogData implements ProcessorInterface
{
    public function __construct(private SensitiveDataRedactor $redactor) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        return $record->with(
            context: $this->redactor->redact($record->context),
            extra: $this->redactor->redact($record->extra),
        );
    }
}
