<?php

namespace App\Domains\Pricing\Models;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Identity\Models\User;
use App\Domains\Shared\Models\Concerns\AppendOnly;
use Database\Factories\Pricing\PricingOverrideLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('pricing_overrides_log')]
#[Fillable(['actor_id', 'product_variant_id', 'previous_price_base', 'new_price_base', 'floor_price_base', 'reason'])]
#[UseFactory(PricingOverrideLogFactory::class)]
class PricingOverrideLog extends Model
{
    /** @use HasFactory<PricingOverrideLogFactory> */
    use AppendOnly, HasFactory;

    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'previous_price_base' => 'decimal:6',
            'new_price_base' => 'decimal:6',
            'floor_price_base' => 'decimal:6',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
