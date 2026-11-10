<?php

namespace Database\Factories\Automation;

use App\Domains\Automation\Enums\AiSuggestionStatus;
use App\Domains\Automation\Enums\AiSuggestionType;
use App\Domains\Automation\Models\AiSuggestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiSuggestion>
 */
class AiSuggestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workflow_id' => fake()->bothify('A##??'),
            'type' => AiSuggestionType::Product,
            'content' => ['key' => 'value'],
            'status' => AiSuggestionStatus::PendingReview,
        ];
    }
}
