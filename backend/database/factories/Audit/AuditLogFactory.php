<?php

namespace Database\Factories\Audit;

use App\Domains\Audit\Enums\AuditLogActorType;
use App\Domains\Audit\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_type' => AuditLogActorType::Staff,
            'action' => fake()->lexify('????????????'),
        ];
    }
}
