<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Enums\ProductCountryRestrictionMode;
use App\Domains\Shared\Models\Country;
use Database\Factories\Catalog\ProductCountryRestrictionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('product_country_restrictions')]
#[Fillable(['product_id', 'country_code', 'mode'])]
#[UseFactory(ProductCountryRestrictionFactory::class)]
class ProductCountryRestriction extends Model
{
    /** @use HasFactory<ProductCountryRestrictionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mode' => ProductCountryRestrictionMode::class,
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }
}
