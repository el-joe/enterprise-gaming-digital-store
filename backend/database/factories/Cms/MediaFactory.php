<?php

namespace Database\Factories\Cms;

use App\Domains\Cms\Enums\MediaSource;
use App\Domains\Cms\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'disk' => 'public',
            'path' => '/'.fake()->slug(3),
            'original_filename' => fake()->words(2, true),
            'mime' => 'image/webp',
            'size_bytes' => fake()->numberBetween(1, 1000),
            'source' => MediaSource::Upload,
        ];
    }
}
