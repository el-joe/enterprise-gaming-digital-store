<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Analytics\Enums\DailySalesSummaryDimension;
use Database\Factories\Analytics\DailySalesSummaryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('daily_sales_summaries')]
#[Fillable(['date', 'dimension', 'dimension_key', 'units_count'])]
#[UseFactory(DailySalesSummaryFactory::class)]
class DailySalesSummary extends Model
{
    /** @use HasFactory<DailySalesSummaryFactory> */
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
            'dimension' => DailySalesSummaryDimension::class,
            'orders_count' => 'integer',
            'units_count' => 'integer',
            'revenue_base' => 'decimal:6',
            'refunds_base' => 'decimal:6',
            'supplier_cost_base' => 'decimal:6',
            'gateway_fee_base' => 'decimal:6',
            'gross_profit_base' => 'decimal:6',
            'net_profit_base' => 'decimal:6',
        ];
    }
}
