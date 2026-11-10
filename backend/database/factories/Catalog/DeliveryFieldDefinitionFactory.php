<?php

namespace Database\Factories\Catalog;

use App\Domains\Catalog\Enums\DeliveryFieldDefinitionInputType;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryFieldDefinition>
 */
class DeliveryFieldDefinitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('????????????????'),
            'label' => fake()->words(2, true),
            'input_type' => DeliveryFieldDefinitionInputType::Text,
        ];
    }
}
