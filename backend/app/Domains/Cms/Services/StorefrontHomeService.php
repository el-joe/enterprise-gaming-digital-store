<?php

namespace App\Domains\Cms\Services;

use App\Domains\Catalog\DTOs\ProductCard;
use App\Domains\Catalog\DTOs\ProductListingFilter;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Services\StorefrontCatalogService;
use App\Domains\Cms\DTOs\HomeItem;
use App\Domains\Cms\DTOs\HomePage;
use App\Domains\Cms\DTOs\HomeSection;
use App\Domains\Cms\Enums\BannerPlacement;
use App\Domains\Cms\Enums\HomepageAutoRule;
use App\Domains\Cms\Enums\HomepageSectionSource;
use App\Domains\Cms\Models\Banner;
use App\Domains\Cms\Models\Faq;
use App\Domains\Cms\Models\HomepageSection;
use App\Domains\Cms\Models\HomepageSectionItem;
use App\Domains\Reviews\Services\ReviewService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * The storefront home (M-A4, gap G7): active homepage sections in order with their content
 * resolved, and live banners (active and inside their schedule). Manual items that are no
 * longer visible (inactive, unpublished, out of schedule, deleted) are dropped. Every product,
 * manual or automatic, becomes a card priced by StorefrontCatalogService::cards, the listing's
 * pricing path, in one batch.
 */
class StorefrontHomeService
{
    private const int DEFAULT_LIMIT = 12;

    private const int MAX_LIMIT = 24;

    /** Manual item types the admin may attach (ReplaceHomepageSectionItemsRequest). */
    private const array ITEM_TYPES = ['product', 'gaming_platform', 'category', 'banner', 'faq'];

    public function __construct(
        private StorefrontCatalogService $catalog,
        private ReviewService $reviews,
    ) {}

    public function build(string $displayCurrency): HomePage
    {
        $sections = HomepageSection::query()
            ->where('is_active', true)
            ->with(['homepageSectionItems' => static fn ($items) => $items->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $ids = array_fill_keys(self::ITEM_TYPES, []);

        foreach ($sections->where('source', HomepageSectionSource::Manual) as $section) {
            foreach ($section->homepageSectionItems as $item) {
                if (array_key_exists($item->item_type, $ids)) {
                    $ids[$item->item_type][] = $item->item_id;
                }
            }
        }

        $manualProducts = $this->catalog->listedProducts(array_values(array_unique($ids['product'])));
        $platforms = $this->platforms($ids['gaming_platform']);
        $categories = $this->categories($ids['category']);
        $banners = $this->liveBanners($ids['banner']);
        $faqs = $this->faqs($ids['faq']);

        $auto = [];
        $allProducts = $manualProducts->values();

        foreach ($sections->where('source', HomepageSectionSource::Auto) as $section) {
            $rule = $this->ruleOf($section);
            $limit = $this->limitOf($section);

            $auto[$section->id] = match (true) {
                $rule === null => [],
                $rule->listsProducts() => $this->catalog->topProducts($this->filterOf($section, $rule), $limit),
                $rule === HomepageAutoRule::Reviews => $this->reviews->featuredForHome($limit),
                default => Faq::query()->where('is_active', true)->where('show_on_homepage', true)->orderBy('sort_order')->orderBy('id')->limit($limit)->get(),
            };

            if ($rule?->listsProducts() === true) {
                $allProducts = $allProducts->merge($auto[$section->id]);
            }
        }

        $cards = $this->catalog->cards($allProducts->unique('id'), $displayCurrency)
            ->keyBy(static fn (ProductCard $card): int => $card->product->id);

        $resolved = [];

        foreach ($sections as $section) {
            if ($section->source === HomepageSectionSource::Manual) {
                $items = $section->homepageSectionItems
                    ->map(static fn (HomepageSectionItem $item): ?HomeItem => match ($item->item_type) {
                        'product' => ($card = $cards->get($item->item_id)) === null ? null : new HomeItem('product', $card),
                        'gaming_platform' => ($platform = $platforms->get($item->item_id)) === null ? null : new HomeItem('platform', $platform),
                        'category' => ($category = $categories->get($item->item_id)) === null ? null : new HomeItem('category', $category),
                        'banner' => ($banner = $banners->get($item->item_id)) === null ? null : new HomeItem('banner', $banner),
                        'faq' => ($faq = $faqs->get($item->item_id)) === null ? null : new HomeItem('faq', $faq),
                        default => null,
                    })
                    ->filter()
                    ->values()
                    ->all();

                $resolved[] = new HomeSection($section, null, $items);

                continue;
            }

            $rule = $this->ruleOf($section);
            $items = [];

            foreach ($auto[$section->id] ?? [] as $model) {
                $items[] = match (true) {
                    $model instanceof Product => new HomeItem('product', $cards->get($model->id)),
                    $model instanceof Faq => new HomeItem('faq', $model),
                    default => new HomeItem('review', $model),
                };
            }

            $resolved[] = new HomeSection($section, $rule, $items);
        }

        $byPlacement = [];

        foreach ([BannerPlacement::Hero, BannerPlacement::HomePromo] as $placement) {
            $byPlacement[$placement->value] = $banners->where('placement', $placement)->sortBy([['sort_order', 'asc'], ['id', 'asc']])->values()->all();
        }

        return new HomePage($resolved, $byPlacement);
    }

    private function ruleOf(HomepageSection $section): ?HomepageAutoRule
    {
        $configured = $section->settings['rule'] ?? null;

        return (is_string($configured) ? HomepageAutoRule::tryFrom($configured) : null) ?? HomepageAutoRule::defaultFor($section->key);
    }

    private function limitOf(HomepageSection $section): int
    {
        $limit = $section->settings['limit'] ?? null;

        return is_int($limit) ? max(1, min(self::MAX_LIMIT, $limit)) : self::DEFAULT_LIMIT;
    }

    private function filterOf(HomepageSection $section, HomepageAutoRule $rule): ProductListingFilter
    {
        $setting = static fn (string $key): ?string => is_string($section->settings[$key] ?? null) && $section->settings[$key] !== '' ? $section->settings[$key] : null;

        return new ProductListingFilter(
            category: $setting('category'),
            platform: $setting('platform'),
            deliveryType: $setting('delivery_type'),
            sort: $rule->sort(),
            flag: $rule->flag(),
        );
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, GamingPlatform>
     */
    private function platforms(array $ids): Collection
    {
        return $ids === [] ? new Collection : GamingPlatform::query()->where('is_active', true)->whereIn('id', $ids)->with(['logoMedia', 'bannerMedia'])->get()->keyBy('id');
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, Category>
     */
    private function categories(array $ids): Collection
    {
        return $ids === [] ? new Collection : Category::query()->where('is_active', true)->whereIn('id', $ids)->with('bannerMedia')->get()->keyBy('id');
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, Faq>
     */
    private function faqs(array $ids): Collection
    {
        return $ids === [] ? new Collection : Faq::query()->where('is_active', true)->whereIn('id', $ids)->get()->keyBy('id');
    }

    /**
     * Live banners: the hero and home-promo placements plus any banner a section names, active
     * and inside `starts_at` (inclusive) to `ends_at` (exclusive).
     *
     * @param  list<int>  $ids
     * @return Collection<int, Banner>
     */
    private function liveBanners(array $ids): Collection
    {
        $now = now();

        return Banner::query()
            ->where('is_active', true)
            ->where(static fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(static fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', $now))
            ->where(static fn (Builder $query) => $query->whereIn('placement', [BannerPlacement::Hero, BannerPlacement::HomePromo])->when($ids !== [], static fn (Builder $inner) => $inner->orWhereIn('id', $ids)))
            ->with(['media', 'mobileMedia'])
            ->get()
            ->keyBy('id');
    }
}
