<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Analytics\Enums\SearchConsoleMetricDimension;
use Database\Factories\Analytics\SearchConsoleMetricFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('search_console_metrics')]
#[Fillable(['date', 'dimension', 'key_hash', 'key_value', 'clicks', 'impressions', 'ctr', 'position'])]
#[UseFactory(SearchConsoleMetricFactory::class)]
class SearchConsoleMetric extends Model
{
    /** @use HasFactory<SearchConsoleMetricFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'dimension' => SearchConsoleMetricDimension::class,
            'clicks' => 'integer',
            'impressions' => 'integer',
            'ctr' => 'decimal:6',
            'position' => 'decimal:3',
        ];
    }
}
