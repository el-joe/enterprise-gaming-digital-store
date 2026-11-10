<?php

namespace App\Domains\Pricing\Models;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Models\Currency;
use Database\Factories\Pricing\VariantPriceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('variant_prices')]
#[Fillable(['product_variant_id', 'country_code', 'currency', 'price', 'compare_at', 'starts_at', 'ends_at'])]
#[UseFactory(VariantPriceFactory::class)]
class VariantPrice extends Model
{
    /** @use HasFactory<VariantPriceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:6',
            'compare_at' => 'decimal:6',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }
}
