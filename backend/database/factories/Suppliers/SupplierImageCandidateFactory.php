<?php

namespace Database\Factories\Suppliers;

use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Cms\Enums\MediaSource;
use App\Domains\Cms\Models\Media;
use App\Domains\Suppliers\Enums\SupplierImageKind;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierImageCandidate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierImageCandidate>
 */
class SupplierImageCandidateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_type' => 'gaming_platform',
            'subject_id' => GamingPlatform::factory(),
            'supplier_id' => Supplier::factory(),
            'kind' => SupplierImageKind::BrandLogo,
            'source_url' => 'https://cdn.reloadly.com/brands/'.fake()->unique()->slug(2).'.png',
            'image_hash' => hash('sha256', fake()->uuid()),
            'media_id' => Media::factory()->state(['source' => MediaSource::Supplier]),
            'fetched_at' => now(),
            'error' => null,
        ];
    }
}
