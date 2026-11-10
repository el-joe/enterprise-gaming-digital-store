<?php

namespace App\Domains\Cms\DTOs;

use App\Domains\Cms\Enums\HomepageAutoRule;
use App\Domains\Cms\Models\HomepageSection;

/**
 * An active homepage section with its items resolved (manual items, or the auto rule's result).
 */
final readonly class HomeSection
{
    /**
     * @param  list<HomeItem>  $items
     */
    public function __construct(
        public HomepageSection $section,
        public ?HomepageAutoRule $rule,
        public array $items,
    ) {}
}
