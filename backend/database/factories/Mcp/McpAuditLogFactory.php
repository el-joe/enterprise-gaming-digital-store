<?php

namespace Database\Factories\Mcp;

use App\Domains\Mcp\Enums\McpAuditLogOutcome;
use App\Domains\Mcp\Models\McpAuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<McpAuditLog>
 */
class McpAuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tool' => fake()->words(2, true),
            'outcome' => McpAuditLogOutcome::Ok,
        ];
    }
}
