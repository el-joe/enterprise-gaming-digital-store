<?php

namespace Database\Factories\Payments;

use App\Domains\Payments\Enums\ReconciliationItemStatus;
use App\Domains\Payments\Enums\ReconciliationItemType;
use App\Domains\Payments\Models\ReconciliationItem;
use App\Domains\Payments\Models\ReconciliationRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReconciliationItem>
 */
class ReconciliationItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reconciliation_run_id' => ReconciliationRun::factory(),
            'type' => ReconciliationItemType::OrphanProvider,
            'details' => ['key' => 'value'],
            'status' => ReconciliationItemStatus::Open,
        ];
    }
}
