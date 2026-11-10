<?php

namespace Database\Factories\Affiliates;

use App\Domains\Affiliates\Enums\AffiliateReferralStatus;
use App\Domains\Affiliates\Models\Affiliate;
use App\Domains\Affiliates\Models\AffiliateReferral;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AffiliateReferral>
 */
class AffiliateReferralFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'visitor_token_hash' => fake()->sha256(),
            'status' => AffiliateReferralStatus::Clicked,
            'clicked_at' => now(),
        ];
    }
}
