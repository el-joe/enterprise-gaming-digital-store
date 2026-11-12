<?php

/*
| A database queue job is handed to another worker after `retry_after` seconds, so it must be
| longer than the longest job timeout or a long catalog sync runs twice.
*/

it('keeps the database queue retry_after above every job timeout', function (): void {
    $longest = 0;
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path(), FilesystemIterator::SKIP_DOTS));

    foreach ($files as $file) {
        if ($file->getExtension() === 'php' && preg_match('/public\s+int\s+\$timeout\s*=\s*(\d+)\s*;/', (string) file_get_contents($file->getPathname()), $match) === 1) {
            $longest = max($longest, (int) $match[1]);
        }
    }

    expect($longest)->toBeGreaterThanOrEqual(600)
        ->and((int) config('queue.connections.database.retry_after'))->toBeGreaterThan($longest);
});
