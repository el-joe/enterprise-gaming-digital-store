<?php

namespace Database\Factories\Catalog;

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gaming_platform_id' => GamingPlatform::factory(),
            'primary_category_id' => Category::factory(),
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(3),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-????')),
            'delivery_type' => DeliveryType::InstantCode,
        ];
    }
}
