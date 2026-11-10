<?php

namespace App\Domains\Pricing\Services;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Pricing\DTOs\CouponTerms;
use App\Domains\Pricing\DTOs\PricingDataset;
use App\Domains\Promotions\Enums\CouponType;
use App\Domains\Promotions\Enums\PromotionTargetTargetType;
use App\Domains\Promotions\Enums\PromotionType;
use App\Domains\Promotions\Models\Promotion;
use App\Domains\Promotions\Models\PromotionTarget;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Shared\ValueObjects\Percentage;
use Illuminate\Support\Collection;

/**
 * Promotion and coupon arithmetic (steps 5 and 6). A discount never takes a price below zero.
 */
class DiscountCalculator
{
    /**
     * The best single eligible promotion. Others are added only when the best one is stackable,
     * and then only those marked stackable.
     *
     * @return array{0: Money, 1: list<int>} the price after promotions and the promotion ids used
     */
    public function applyPromotions(Money $price, ProductVariant $variant, PricingDataset $data): array
    {
        $eligible = $data->promotions
            ->filter(fn (Promotion $promotion): bool => $this->targets($promotion, $variant))
            ->sort(fn (Promotion $left, Promotion $right): int => $this->discount($right, $price)->compareTo($this->discount($left, $price))
                ?: $left->priority <=> $right->priority)
            ->values();

        /** @var Promotion|null $best */
        $best = $eligible->first();

        if ($best === null) {
            return [$price, []];
        }

        /** @var Collection<int, Promotion> $applied */
        $applied = $best->stackable
            ? $eligible->filter(static fn (Promotion $promotion): bool => $promotion->stackable)
            : collect([$best]);

        $result = $price;

        foreach ($applied as $promotion) {
            $result = $this->positive($result->minus($this->discount($promotion, $result)));
        }

        return [$result, $applied->pluck('id')->values()->all()];
    }

    public function couponDiscount(Money $price, CouponTerms $coupon): Money
    {
        $discount = $coupon->type === CouponType::Percent
            ? $price->multipliedBy($coupon->value)->dividedBy('100')
            : Money::of($coupon->value, $price->currency);

        if ($coupon->maxDiscountBase !== null && $discount->compareTo($coupon->maxDiscountBase) === 1) {
            $discount = $coupon->maxDiscountBase;
        }

        return $discount->compareTo($price) === 1 ? $price : $discount;
    }

    private function discount(Promotion $promotion, Money $price): Money
    {
        $discount = match ($promotion->type) {
            PromotionType::Percent => Percentage::of(bcadd($promotion->value, '0', 4))->applyTo($price),
            PromotionType::Fixed => Money::of($promotion->value, $price->currency),
            PromotionType::FixedPrice => $this->positive($price->minus(Money::of($promotion->value, $price->currency))),
        };

        return $discount->compareTo($price) === 1 ? $price : $discount;
    }

    private function targets(Promotion $promotion, ProductVariant $variant): bool
    {
        return $promotion->promotionTargets->contains(static fn (PromotionTarget $target): bool => match ($target->target_type) {
            PromotionTargetTargetType::Variant => $target->target_id === $variant->id,
            PromotionTargetTargetType::Product => $target->target_id === $variant->product_id,
            PromotionTargetTargetType::Platform => $target->target_id === $variant->product->gaming_platform_id,
            PromotionTargetTargetType::Category => $target->target_id === $variant->product->primary_category_id
                || $variant->product->categories->contains('id', $target->target_id),
        });
    }

    private function positive(Money $money): Money
    {
        return $money->isNegative() ? Money::zero($money->currency) : $money;
    }
}
