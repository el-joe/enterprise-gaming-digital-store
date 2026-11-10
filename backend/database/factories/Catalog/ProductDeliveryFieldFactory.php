<?php

namespace Database\Factories\Catalog;

use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductDeliveryField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductDeliveryField>
 */
class ProductDeliveryFieldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'delivery_field_definition_id' => DeliveryFieldDefinition::factory(),
        ];
    }
}
