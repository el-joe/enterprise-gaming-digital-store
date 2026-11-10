<?php

namespace App\Domains\Pricing\Services;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Pricing\DTOs\PricingContext;
use App\Domains\Pricing\DTOs\PricingDataset;
use App\Domains\Pricing\Models\PricingRule;
use App\Domains\Pricing\Models\VariantPrice;
use App\Domains\Promotions\Models\Promotion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Stores the listing price on product_variants.price_base. Workers skip rows another worker
 * holds, and a variant re-marked after it was read stays dirty for the next run.
 */
class VariantPriceRecalculator
{
    private const int CHUNK = 100;

    private const string WINDOW_SWEEP_CACHE_KEY = 'pricing:window_sweep_at';

    public function __construct(
        private PricingEngine $engine,
        private PricingDataLoader $loader,
        private PricingDirtyMarker $dirty,
    ) {}

    /**
     * @return array{recalculated: int, skipped: int}
     */
    public function run(): array
    {
        $this->markRulesThatCrossedTheirWindow();

        $recalculated = 0;
        $skipped = 0;
        $cursor = 0;

        while (true) {
            $variants = ProductVariant::query()
                ->whereNotNull('pricing_dirty_at')
                ->where('id', '>', $cursor)
                ->orderBy('id')
                ->limit(self::CHUNK)
                ->get();

            if ($variants->isEmpty()) {
                return ['recalculated' => $recalculated, 'skipped' => $skipped];
            }

            $data = $this->loader->load($variants);

            foreach ($variants as $variant) {
                $this->recalculate($variant, $data) ? $recalculated++ : $skipped++;
            }

            $cursor = (int) $variants->last()->id;
        }
    }

    private function recalculate(ProductVariant $variant, PricingDataset $data): bool
    {
        return DB::transaction(function () use ($variant, $data): bool {
            $locked = ProductVariant::query()->whereKey($variant->id)->lock('FOR UPDATE SKIP LOCKED')->first();

            if ($locked === null || $locked->pricing_dirty_at?->getTimestamp() !== $variant->pricing_dirty_at?->getTimestamp()) {
                return false;
            }

            $locked->forceFill(['price_base' => $this->listingPrice($variant, $data), 'pricing_dirty_at' => null])->save();

            return true;
        });
    }

    private function listingPrice(ProductVariant $variant, PricingDataset $data): ?string
    {
        $quote = $this->engine->quoteWith($variant, new PricingContext, $data);

        if (! $quote->purchasable) {
            Log::info('pricing.listing_price_unavailable', ['variant_id' => $variant->id, 'reason' => $quote->blockReason]);
        }

        return $quote->purchasable ? $quote->baseAmount?->amount : null;
    }

    /**
     * Time-boxed rules, explicit prices and promotions change listing prices with no write to
     * trigger a mark, so any whose start or end passed since the last sweep is marked here.
     */
    private function markRulesThatCrossedTheirWindow(): void
    {
        $since = Cache::get(self::WINDOW_SWEEP_CACHE_KEY, now()->subDay()->toDateTimeString());
        $until = now()->toDateTimeString();

        $crossed = static fn ($query) => $query->where(static function ($inner) use ($since, $until): void {
            $inner->whereBetween('starts_at', [$since, $until])->orWhereBetween('ends_at', [$since, $until]);
        });

        $crossed(PricingRule::query())->get()->each(fn (PricingRule $rule) => $this->dirty->forRule($rule));

        $variantIds = $crossed(VariantPrice::query())->pluck('product_variant_id')->unique()->values()->all();
        $this->dirty->forVariants($variantIds);

        if ($crossed(Promotion::query())->exists()) {
            $this->dirty->all();
        }

        Cache::forever(self::WINDOW_SWEEP_CACHE_KEY, $until);
    }
}
