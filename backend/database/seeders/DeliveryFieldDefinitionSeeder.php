<?php

namespace Database\Seeders;

use App\Domains\Catalog\Enums\DeliveryFieldDefinitionInputType;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use Illuminate\Database\Seeder;

class DeliveryFieldDefinitionSeeder extends Seeder
{
    /**
     * Seed the reusable delivery field library.
     */
    public function run(): void
    {
        $fields = [
            'player_id' => 'Player ID',
            'server_id' => 'Server ID',
            'region' => 'Region',
            'username' => 'Username',
            'account_identifier' => 'Account identifier',
        ];

        foreach ($fields as $code => $label) {
            DeliveryFieldDefinition::query()->updateOrCreate(['code' => $code], [
                'label' => $label,
                'input_type' => DeliveryFieldDefinitionInputType::Text,
            ]);
        }
    }
}
