<?php

namespace App\Domains\Settings\Services\Export;

/**
 * Writes an export file row by row (M14). Implementations neutralise spreadsheet formulas.
 */
interface TableWriter
{
    public function open(string $path): void;

    /**
     * @param  list<string>  $cells
     */
    public function row(array $cells): void;

    public function close(): void;

    public function extension(): string;

    public function mimeType(): string;
}
