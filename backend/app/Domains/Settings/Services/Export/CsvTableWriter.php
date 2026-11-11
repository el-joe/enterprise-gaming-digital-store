<?php

namespace App\Domains\Settings\Services\Export;

use RuntimeException;

/**
 * UTF-8 CSV with a byte order mark, so spreadsheet programs read Arabic text correctly.
 */
class CsvTableWriter implements TableWriter
{
    /** @var resource|null */
    private $handle = null;

    public function open(string $path): void
    {
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new RuntimeException('The export file could not be opened.');
        }

        fwrite($handle, "\xEF\xBB\xBF");
        $this->handle = $handle;
    }

    public function row(array $cells): void
    {
        fputcsv($this->handle, array_map(FormulaGuard::clean(...), $cells), ',', '"', '');
    }

    public function close(): void
    {
        if ($this->handle !== null) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    public function extension(): string
    {
        return 'csv';
    }

    public function mimeType(): string
    {
        return 'text/csv';
    }
}
