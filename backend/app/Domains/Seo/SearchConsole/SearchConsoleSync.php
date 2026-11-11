<?php

namespace App\Domains\Seo\SearchConsole;

use App\Domains\Analytics\Enums\SearchConsoleMetricDimension;
use App\Domains\Analytics\Services\AnalyticsSettings;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Daily Search Console reporting (M18): totals per day, and per day the top queries and top
 * landing pages, with clicks, impressions, CTR and average position, upserted into
 * `search_console_metrics` (unique per date, dimension and key). The window ends three days ago
 * (Search Console's final data lags) and re-reads earlier days so late changes land. The API
 * returns top rows only and does not guarantee all data; the report says so.
 */
class SearchConsoleSync
{
    private const int LAG_DAYS = 3;

    private const int ROW_LIMIT = 5000;

    public function __construct(private SearchConsoleClient $client, private AnalyticsSettings $settings) {}

    public function ready(): bool
    {
        return $this->settings->searchConsoleReady();
    }

    /**
     * @return int rows written
     *
     * @throws SearchConsoleException
     */
    public function sync(int $days = 3): int
    {
        if (! $this->ready()) {
            throw new SearchConsoleException(__('admin.search_console.not_ready'));
        }

        $end = CarbonImmutable::today()->subDays(self::LAG_DAYS);
        $start = $end->subDays(max(1, min(90, $days)) - 1);
        $written = 0;

        $plan = [
            SearchConsoleMetricDimension::Total->value => ['date'],
            SearchConsoleMetricDimension::Query->value => ['date', 'query'],
            SearchConsoleMetricDimension::Page->value => ['date', 'page'],
        ];

        foreach ($plan as $dimension => $dimensions) {
            $rows = $this->client->query($start->toDateString(), $end->toDateString(), $dimensions, self::ROW_LIMIT);
            $records = [];

            foreach ($rows as $row) {
                $keys = array_values((array) ($row['keys'] ?? []));
                $date = $keys[0] ?? null;

                if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    continue;
                }

                $key = $dimension === SearchConsoleMetricDimension::Total->value ? 'total' : mb_substr((string) ($keys[1] ?? ''), 0, 1000);

                $records[] = [
                    'date' => $date,
                    'dimension' => $dimension,
                    'key_hash' => hash('sha256', $key),
                    'key_value' => $key,
                    'clicks' => (int) ($row['clicks'] ?? 0),
                    'impressions' => (int) ($row['impressions'] ?? 0),
                    'ctr' => number_format((float) ($row['ctr'] ?? 0), 6, '.', ''),
                    'position' => number_format(min(9999.999, (float) ($row['position'] ?? 0)), 3, '.', ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($records, 500) as $chunk) {
                DB::table('search_console_metrics')->upsert($chunk, ['date', 'dimension', 'key_hash'], ['key_value', 'clicks', 'impressions', 'ctr', 'position', 'updated_at']);
            }

            $written += count($records);
        }

        return $written;
    }
}
