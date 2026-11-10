<?php

namespace App\Domains\Catalog\Models;

use Database\Factories\Catalog\ProductDeliveryFieldFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('product_delivery_fields')]
#[Fillable(['product_id', 'delivery_field_definition_id', 'is_required', 'sort_order'])]
#[UseFactory(ProductDeliveryFieldFactory::class)]
class ProductDeliveryField extends Model
{
    /** @use HasFactory<ProductDeliveryFieldFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'sort_order' => 'integer',
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
     * @return BelongsTo<DeliveryFieldDefinition, $this>
     */
    public function deliveryFieldDefinition(): BelongsTo
    {
        return $this->belongsTo(DeliveryFieldDefinition::class, 'delivery_field_definition_id');
    }
}
