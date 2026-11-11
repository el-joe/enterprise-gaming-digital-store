<?php

namespace App\Domains\Seo\Services;

use Illuminate\Support\Facades\DB;

/**
 * Counts storefront 404s per path in `not_found_logs` (SEO_STRATEGY.md section 7, M18), so the
 * admin can add redirects for the paths people and crawlers still ask for. One upsert per miss;
 * paths are stored normalised and cut to the column length; the query string is never stored.
 */
class NotFoundLogger
{
    public function record(string $path): void
    {
        $normalized = mb_substr(RedirectService::normalize($path), 0, 500);
        $now = now();

        DB::table('not_found_logs')->upsert(
            [['path_hash' => hash('sha256', $normalized), 'path' => $normalized, 'hits' => 1, 'last_seen_at' => $now, 'created_at' => $now, 'updated_at' => $now]],
            ['path_hash'],
            ['hits' => DB::raw('hits + 1'), 'last_seen_at' => $now, 'updated_at' => $now],
        );
    }
}
