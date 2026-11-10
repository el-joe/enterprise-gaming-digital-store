<?php

namespace App\Domains\Pricing\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Identity\Models\User;
use App\Domains\Pricing\Models\PricingOverrideLog;
use App\Domains\Pricing\Models\VariantPrice;
use App\Domains\Shared\Services\CrudService;
use App\Domains\Shared\Services\ListQueryApplier;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * @extends CrudService<VariantPrice>
 */
class VariantPriceService extends CrudService
{
    public function __construct(
        ListQueryApplier $lists,
        AuditLogger $audit,
        private PricingEngine $engine,
        private CurrencyConverter $converter,
        private ExchangeRateRegistry $rates,
        private PricingDirtyMarker $dirty,
        private MarginAuthorizer $margin,
    ) {
        parent::__construct($lists, $audit);
    }

    protected function model(): string
    {
        return VariantPrice::class;
    }

    protected function auditPrefix(): string
    {
        return 'pricing.variant_price';
    }

    protected function filters(): array
    {
        return ['product_variant_id' => 'exact', 'currency' => 'exact', 'country_code' => 'exact'];
    }

    protected function sorts(): array
    {
        return ['id', 'price', 'starts_at', 'created_at'];
    }

    /**
     * @param  VariantPrice  $model
     */
    protected function prepare(Model $model, array $attributes): array
    {
        $state = [...$model->only(['product_variant_id', 'country_code', 'currency', 'price', 'starts_at']), ...$attributes];
        $state['country_code'] = strtoupper((string) ($state['country_code'] ?? ExplicitPriceResolver::ANY_COUNTRY));
        $state['currency'] = strtoupper((string) $state['currency']);

        $this->assertNoDuplicateWithoutStart($model, $state);

        if (! $model->exists || array_intersect_key($attributes, array_flip(['price', 'currency', 'country_code'])) !== []) {
            $this->guardMargin($model, $state, $attributes['override_reason'] ?? null);
        }

        return [...$attributes, 'country_code' => $state['country_code'], 'currency' => $state['currency']];
    }

    /**
     * @param  VariantPrice  $model
     */
    protected function afterSave(Model $model, array $attributes): void
    {
        $this->dirty->forVariants([$model->product_variant_id]);
    }

    /**
     * @param  VariantPrice  $model
     */
    protected function afterDelete(Model $model): void
    {
        $this->dirty->forVariants([$model->product_variant_id]);
    }

    /**
     * uq_vp does not catch repeats when starts_at is NULL.
     *
     * @param  array<string, mixed>  $state
     */
    private function assertNoDuplicateWithoutStart(VariantPrice $price, array $state): void
    {
        if (($state['starts_at'] ?? null) !== null) {
            return;
        }

        $exists = VariantPrice::query()
            ->where('product_variant_id', $state['product_variant_id'])
            ->where('country_code', $state['country_code'])
            ->where('currency', $state['currency'])
            ->whereNull('starts_at')
            ->when($price->exists, static fn ($query) => $query->whereKeyNot($price->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['currency' => ['An explicit price for this variant, country and currency already exists.']]);
        }
    }

    /**
     * A price below the floor, or one whose margin cannot be verified, needs explicit authorization.
     *
     * @param  array<string, mixed>  $state
     */
    private function guardMargin(VariantPrice $price, array $state, ?string $reason): void
    {
        $variant = ProductVariant::query()->findOrFail($state['product_variant_id']);
        $country = $state['country_code'] === ExplicitPriceResolver::ANY_COUNTRY ? null : $state['country_code'];
        $priceBase = $this->inBase((string) $state['price'], $state['currency']);

        ['floor' => $floor, 'cost_known' => $costKnown] = $this->engine->floorFor($variant, $country, $state['currency']);

        if ($costKnown && $floor !== null && $priceBase->compareTo($floor) !== -1) {
            return;
        }

        $user = Auth::user();

        $this->margin->assertAuthorized(
            $user instanceof User ? $user : null,
            $reason,
            'override_reason',
            $costKnown
                ? 'This price is below the minimum margin and needs the margin override permission and a reason.'
                : 'This variant has no known cost, so its margin cannot be verified. Overriding needs the margin override permission and a reason.',
        );

        $previous = $price->exists ? $this->inBase((string) $price->getOriginal('price'), (string) $price->getOriginal('currency')) : null;

        $log = PricingOverrideLog::query()->create([
            'actor_id' => $user->getAuthIdentifier(),
            'product_variant_id' => $variant->id,
            'previous_price_base' => $previous?->amount,
            'new_price_base' => $priceBase->amount,
            'floor_price_base' => $floor?->amount,
            'reason' => $reason,
        ]);

        $this->audit->record(new AuditEntry(
            action: 'pricing.margin_override',
            subject: $variant,
            before: ['price_base' => $previous?->amount],
            after: ['price_base' => $priceBase->amount, 'floor_base' => $floor?->amount, 'override_log_id' => $log->id],
            reason: $reason,
        ));
    }

    private function inBase(string $price, string $currency): Money
    {
        return $this->converter->toBase(Money::of($price, $currency), $this->rates->resolve($currency), withoutMarkup: true);
    }
}
