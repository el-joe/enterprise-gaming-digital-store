<?php

namespace App\Domains\Cms\DTOs;

use App\Domains\Cms\Models\Banner;

/**
 * The storefront home (M-A4): sections in order and live banners by placement.
 */
final readonly class HomePage
{
    /**
     * @param  list<HomeSection>  $sections
     * @param  array<string, list<Banner>>  $banners  keyed by placement (`hero`, `home_promo`)
     */
    public function __construct(
        public array $sections,
        public array $banners,
    ) {}
}
