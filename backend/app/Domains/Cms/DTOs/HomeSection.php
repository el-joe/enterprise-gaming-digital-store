<?php

namespace App\Domains\Cms\DTOs;

use App\Domains\Catalog\Models\Category;
use App\Domains\Cms\Enums\HomepageAutoRule;
use App\Domains\Cms\Enums\HomepageSectionType;
use App\Domains\Cms\Models\HomepageSection;

/**
 * An active homepage section with its items resolved (manual items, or the auto rule's result).
 * Storefront v2: its type and computed "See all" target; a category rail (a category with
 * "Show on home page", after the managed sections) carries its category and an unsaved section.
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
        public HomepageSectionType $type = HomepageSectionType::ProductRail,
        public ?LinkTarget $seeAll = null,
        public ?Category $category = null,
    ) {}

    public function isCategoryRail(): bool
    {
        return $this->category !== null;
    }
}
