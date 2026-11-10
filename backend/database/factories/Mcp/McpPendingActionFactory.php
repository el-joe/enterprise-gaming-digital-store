<?php

namespace Database\Factories\Mcp;

use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Mcp\Enums\McpPendingActionStatus;
use App\Domains\Mcp\Models\McpPendingAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<McpPendingAction>
 */
class McpPendingActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_account_id' => ServiceAccount::factory(),
            'tool' => fake()->words(2, true),
            'arguments' => ['key' => 'value'],
            'arguments_hash' => fake()->sha256(),
            'risk_tier' => 1,
            'summary' => fake()->sentence(4),
            'status' => McpPendingActionStatus::Pending,
            'expires_at' => now(),
        ];
    }
}
