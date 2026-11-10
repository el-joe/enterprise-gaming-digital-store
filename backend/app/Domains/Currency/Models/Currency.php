<?php

namespace App\Domains\Currency\Models;

use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('currencies', key: 'code', keyType: 'string', incrementing: false)]
#[Fillable(['code', 'name', 'symbol', 'exponent', 'rounding_rule', 'markup_pct', 'is_active', 'sort_order'])]
#[UseFactory(CurrencyFactory::class)]
class Currency extends Model
{
    /** @use HasFactory<CurrencyFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'exponent' => 'integer',
            'rounding_rule' => 'array',
            'markup_pct' => 'decimal:4',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
