<?php

namespace Database\Factories\Catalog;

use App\Domains\Catalog\Enums\ProductRelationType;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductRelation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductRelation>
 */
class ProductRelationFactory extends Factory
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
            'related_product_id' => Product::factory(),
            'type' => ProductRelationType::Related,
        ];
    }
}
