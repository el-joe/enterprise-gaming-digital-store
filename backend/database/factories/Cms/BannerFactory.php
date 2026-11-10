<?php

namespace Database\Factories\Cms;

use App\Domains\Cms\Enums\BannerPlacement;
use App\Domains\Cms\Models\Banner;
use App\Domains\Cms\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'placement' => BannerPlacement::Hero,
            'media_id' => Media::factory(),
            'is_active' => true,
        ];
    }
}
