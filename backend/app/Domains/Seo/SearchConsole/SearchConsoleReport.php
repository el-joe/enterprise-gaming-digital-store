<?php

namespace App\Domains\Seo\SearchConsole;

use App\Domains\Analytics\Enums\SearchConsoleMetricDimension;
use App\Domains\Analytics\Models\SearchConsoleMetric;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * The admin Search Console report (M18): clicks, impressions, CTR and average position over a
 * window, with the top queries and top landing pages, from `search_console_metrics`. CTR is
 * clicks over impressions and the position is impression-weighted, so sums stay meaningful.
 */
class SearchConsoleReport
{
    private const int TOP = 20;

    /**
     * @return array{from: string|null, to: string|null, totals: array{clicks: int, impressions: int, ctr: string, position: string}, queries: list<array<string, mixed>>, pages: list<array<string, mixed>>, last_synced: string|null}
     */
    public function summary(int $days): array
    {
        $latest = SearchConsoleMetric::query()->max('date');
        $to = $latest === null ? null : CarbonImmutable::parse($latest);
        $from = $to?->subDays($days - 1);

        $totals = ['clicks' => 0, 'impressions' => 0, 'ctr' => '0.00', 'position' => '0.0'];

        if ($to !== null) {
            $row = $this->aggregate(SearchConsoleMetricDimension::Total, $from, $to)->first();
            $totals = $row === null ? $totals : $this->metrics($row);
        }

        return [
            'from' => $from?->toDateString(),
            'to' => $to?->toDateString(),
            'totals' => $totals,
            'queries' => $to === null ? [] : $this->top(SearchConsoleMetricDimension::Query, $from, $to),
            'pages' => $to === null ? [] : $this->top(SearchConsoleMetricDimension::Page, $from, $to),
            'last_synced' => SearchConsoleMetric::query()->max('updated_at'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function top(SearchConsoleMetricDimension $dimension, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $this->aggregate($dimension, $from, $to)
            ->addSelect(DB::raw('MAX(key_value) as key_value'))
            ->groupBy('key_hash')
            ->orderByDesc('clicks')
            ->orderByDesc('impressions')
            ->limit(self::TOP)
            ->get()
            ->map(fn (object $row): array => ['key' => (string) $row->key_value, ...$this->metrics($row)])
            ->all();
    }

    private function aggregate(SearchConsoleMetricDimension $dimension, CarbonImmutable $from, CarbonImmutable $to): Builder
    {
        return DB::table('search_console_metrics')
            ->where('dimension', $dimension->value)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->select([
                DB::raw('SUM(clicks) as clicks'),
                DB::raw('SUM(impressions) as impressions'),
                DB::raw('SUM(position * impressions) as weighted_position'),
            ]);
    }

    /**
     * @return array{clicks: int, impressions: int, ctr: string, position: string}
     */
    private function metrics(object $row): array
    {
        $clicks = (int) $row->clicks;
        $impressions = (int) $row->impressions;

        return [
            'clicks' => $clicks,
            'impressions' => $impressions,
            'ctr' => $impressions === 0 ? '0.00' : number_format($clicks / $impressions * 100, 2, '.', ''),
            'position' => $impressions === 0 ? '0.0' : number_format((float) $row->weighted_position / $impressions, 1, '.', ''),
        ];
    }
}
