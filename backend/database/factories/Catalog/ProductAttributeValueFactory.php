<?php

namespace Database\Factories\Catalog;

use App\Domains\Catalog\Models\AttributeDefinition;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductAttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductAttributeValue>
 */
class ProductAttributeValueFactory extends Factory
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
            'attribute_definition_id' => AttributeDefinition::factory(),
            'value' => fake()->sentence(4),
        ];
    }
}
