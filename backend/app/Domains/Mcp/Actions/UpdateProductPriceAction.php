<?php

namespace App\Domains\Mcp\Actions;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Identity\Models\User;
use App\Domains\Mcp\Contracts\McpWriteAction;
use App\Domains\Mcp\DTOs\PreparedAction;
use App\Domains\Mcp\Enums\McpWriteTool;
use App\Domains\Mcp\Repositories\McpCatalogRepository;
use App\Domains\Mcp\Repositories\McpPricingRepository;
use App\Domains\Pricing\DTOs\PricingContext;
use App\Domains\Pricing\Services\ExplicitPriceResolver;
use App\Domains\Pricing\Services\PricingEngine;
use App\Domains\Pricing\Services\VariantPriceService;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Shared\Rules\DecimalString;
use App\Domains\Shared\ValueObjects\Money;

/**
 * Sets the explicit base-currency price (country `*`) of a variant through VariantPriceService,
 * so the margin guard applies: a price below the floor needs a staff approver holding
 * `pricing.override_margin`, and is refused on the elicitation path, which has no staff user.
 */
class UpdateProductPriceAction implements McpWriteAction
{
    public function __construct(
        private McpCatalogRepository $catalog,
        private McpPricingRepository $prices,
        private PricingEngine $engine,
        private ExchangeRateRegistry $rates,
        private VariantPriceService $variantPrices,
    ) {}

    public function tool(): McpWriteTool
    {
        return McpWriteTool::UpdateProductPrice;
    }

    public function rules(): array
    {
        return [
            'variant_sku' => ['required', 'string', 'max:80'],
            'new_price_base' => ['required', DecimalString::positiveMoney()],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function prepare(array $validated): PreparedAction
    {
        $variant = $this->variant((string) $validated['variant_sku']);
        $base = $this->rates->baseCurrency();
        $new = Money::of((string) $validated['new_price_base'], $base);
        $current = $this->engine->quote($variant, new PricingContext)->baseAmount;
        ['floor' => $floor, 'cost_known' => $costKnown] = $this->engine->floorFor($variant, null, $base);

        $belowFloor = ! $costKnown || $floor === null || $new->compareTo($floor) === -1;
        $largeChange = $current === null || ! $current->isPositive() || $this->exceedsTier1Change($current, $new);

        $summary = sprintf(
            'Set the base price of variant %s (%s) from %s to %s %s.%s',
            $variant->sku,
            $variant->title,
            $current === null ? 'no current price' : bcadd($current->amount, '0', 2),
            bcadd($new->amount, '0', 2),
            $base,
            $belowFloor ? ' This is below the margin floor or the cost is unknown: approval needs the margin override permission.' : '',
        );

        return new PreparedAction(
            ['variant_sku' => $variant->sku, 'new_price_base' => $new->amount, 'reason' => (string) $validated['reason']],
            $belowFloor || $largeChange ? 2 : 1,
            $summary,
        );
    }

    public function execute(array $arguments, ?User $approver, int $serviceAccountId): array
    {
        $variant = $this->variant((string) $arguments['variant_sku']);
        $base = $this->rates->baseCurrency();
        $previous = $this->engine->quote($variant, new PricingContext)->baseAmount;
        $existing = $this->prices->undatedExplicitPrice($variant, ExplicitPriceResolver::ANY_COUNTRY, $base);

        $attributes = ['price' => (string) $arguments['new_price_base'], 'override_reason' => (string) $arguments['reason']];

        $price = $existing === null
            ? $this->variantPrices->create([...$attributes, 'product_variant_id' => $variant->id, 'country_code' => ExplicitPriceResolver::ANY_COUNTRY, 'currency' => $base])
            : $this->variantPrices->update($existing, $attributes);

        return [
            'variant_sku' => $variant->sku,
            'previous_price_base' => $previous === null ? null : bcadd($previous->amount, '0', 2),
            'new_price_base' => bcadd((string) $price->price, '0', 2),
            'currency' => $base,
            'note' => 'Stored as the explicit base price; derived display prices are recalculated by pricing:recalculate-dirty.',
        ];
    }

    private function variant(string $sku): ProductVariant
    {
        $variant = $this->catalog->findVariantBySku($sku);

        if ($variant === null) {
            throw new ApplicationException('No variant has this SKU.', 'not_found', 404);
        }

        if ($variant->is_custom_amount) {
            throw new ApplicationException('Custom-amount variants have no fixed price.', 'invalid_input', 422);
        }

        return $variant;
    }

    private function exceedsTier1Change(Money $current, Money $new): bool
    {
        $delta = bcsub($new->amount, $current->amount, 6);
        $pct = bcdiv(bcmul(ltrim($delta, '-'), '100', 10), $current->amount, 10);

        return bccomp($pct, (string) config('mcp.price_change_tier1_max_pct'), 10) === 1;
    }
}
