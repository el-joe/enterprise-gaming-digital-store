<?php

namespace App\Domains\Promotions\Services;

use App\Domains\Currency\Exceptions\ExchangeRateMissing;
use App\Domains\Currency\Exceptions\ExchangeRateStale;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Pricing\DTOs\CouponTerms;
use App\Domains\Promotions\Contracts\CheckoutCoupons;
use App\Domains\Promotions\DTOs\CouponLookup;
use App\Domains\Promotions\DTOs\ResolvedCoupon;
use App\Domains\Promotions\Enums\CouponRedemptionStatus;
use App\Domains\Promotions\Enums\CouponType;
use App\Domains\Promotions\Exceptions\CouponInvalid;
use App\Domains\Promotions\Models\Coupon;
use App\Domains\Promotions\Models\CouponRedemption;
use App\Domains\Promotions\Repositories\CouponRepository;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

/**
 * The M11 binding of the M7 coupon contract (ADR-026).
 *
 * `resolve` checks a code against the cart: active and inside its window, the minimum order, the
 * product, category and platform restrictions, first-order-only, and the global and per-buyer
 * limits as they stand. `reserve` runs inside the placement transaction: it locks the coupon
 * row, so every reservation of one coupon is serialised, checks everything again against the
 * locked row and the buyer's held uses, then writes a `reserved` redemption and increments
 * `used_count`. A refusal rolls the whole placement back. A buyer is matched by account and by a
 * keyed hash of the email, so a signed-in customer cannot get round a per-user limit by checking
 * out as a guest. `release` returns the use of an order that was cancelled, expired or failed
 * before payment; it is idempotent. On `paid` the redemption becomes `redeemed`
 * (RedeemCouponWhenPaid). `chk_c_usage` is the database backstop for the global limit.
 *
 * No HTTP is made here; a fixed coupon in another currency is converted with the stored rate.
 */
class DatabaseCheckoutCoupons implements CheckoutCoupons
{
    public function __construct(
        private CouponRepository $coupons,
        private ExchangeRateRegistry $rates,
        private CurrencyConverter $converter,
    ) {}

    public function resolve(CouponLookup $lookup): ResolvedCoupon
    {
        $coupon = $this->coupons->findByCode(self::normalize($lookup->code)) ?? throw new CouponInvalid;

        return $this->validated($coupon, $lookup);
    }

    public function reserve(ResolvedCoupon $coupon, CouponLookup $lookup, int $orderId, Money $discountBase): void
    {
        $locked = $this->coupons->lock($coupon->couponId) ?? throw new CouponInvalid;
        $this->validated($locked, $lookup);

        (new CouponRedemption)->forceFill([
            'coupon_id' => $locked->id,
            'order_id' => $orderId,
            'user_id' => $lookup->userId,
            'email_hash' => self::emailHash($lookup->email),
            'discount_base' => $discountBase->amount,
            'status' => CouponRedemptionStatus::Reserved,
        ])->save();

        Coupon::query()->whereKey($locked->id)->increment('used_count');
    }

    public function release(int $orderId): void
    {
        foreach ($this->coupons->reservedFor($orderId) as $reservation) {
            DB::transaction(function () use ($reservation): void {
                $this->coupons->lock($reservation->coupon_id);
                $locked = $this->coupons->lockRedemption($reservation->id);

                if ($locked === null || $locked->status !== CouponRedemptionStatus::Reserved) {
                    return;
                }

                $locked->forceFill(['status' => CouponRedemptionStatus::Released])->save();
                Coupon::withTrashed()->whereKey($locked->coupon_id)->where('used_count', '>', 0)->decrement('used_count');
            });
        }
    }

    public static function normalize(string $code): string
    {
        return strtoupper(trim($code));
    }

    /**
     * Keyed, so the stored hash cannot be reversed with a list of known addresses.
     */
    public static function emailHash(?string $email): ?string
    {
        $email = $email === null ? '' : mb_strtolower(trim($email));

        return $email === '' ? null : hash_hmac('sha256', $email, (string) config('app.key'));
    }

    private function validated(Coupon $coupon, CouponLookup $lookup): ResolvedCoupon
    {
        $now = now();

        if (! $coupon->is_active || ($coupon->starts_at !== null && $coupon->starts_at->isAfter($now))) {
            throw new CouponInvalid;
        }

        if ($coupon->ends_at !== null && ! $coupon->ends_at->isAfter($now)) {
            throw new CouponInvalid('This coupon has expired.');
        }

        if ($coupon->min_order_base !== null && bccomp($lookup->subtotalBase->amount, $coupon->min_order_base, Money::SCALE) < 0) {
            throw new CouponInvalid('Your order is below the minimum for this coupon.');
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw new CouponInvalid('This coupon has reached its usage limit.');
        }

        $email = $lookup->email === null ? null : mb_strtolower(trim($lookup->email));

        if ($coupon->usage_limit_per_user !== null && $this->coupons->activeUsesBy($coupon->id, $lookup->userId, self::emailHash($email)) >= $coupon->usage_limit_per_user) {
            throw new CouponInvalid('You have already used this coupon.');
        }

        if ($coupon->first_order_only && $this->coupons->hasPaidOrder($lookup->userId, $email)) {
            throw new CouponInvalid('This coupon is for a first order only.');
        }

        $eligible = $this->eligibleVariants($coupon, $lookup);

        return new ResolvedCoupon($coupon->id, $coupon->code, $this->terms($coupon), $eligible);
    }

    /**
     * @return list<int>|null null when the coupon applies to every line
     */
    private function eligibleVariants(Coupon $coupon, CouponLookup $lookup): ?array
    {
        $restriction = array_filter((array) ($coupon->applicable_to ?? []), static fn (mixed $ids): bool => is_array($ids) && $ids !== []);

        if ($restriction === []) {
            return null;
        }

        $eligible = $this->coupons->eligibleVariantIds($restriction, $lookup->productVariantIds);

        if ($eligible === []) {
            throw new CouponInvalid('This coupon does not apply to the items in your cart.');
        }

        return $eligible;
    }

    private function terms(Coupon $coupon): CouponTerms
    {
        $base = $this->rates->baseCurrency();
        $value = (string) $coupon->value;

        if ($coupon->type === CouponType::Fixed && $coupon->currency !== null && $coupon->currency !== $base) {
            try {
                $exponent = $this->rates->currency($base)['exponent'] ?? 2;
                $value = $this->converter->toBase(Money::of($value, $coupon->currency), withoutMarkup: true)->roundToCurrency($exponent)->amount;
            } catch (ExchangeRateMissing|ExchangeRateStale) {
                throw new CouponInvalid('This coupon cannot be used right now.');
            }
        }

        return new CouponTerms(
            type: $coupon->type,
            value: $value,
            maxDiscountBase: $coupon->max_discount_base === null ? null : Money::of($coupon->max_discount_base, $base),
            allowBelowMargin: $coupon->allow_below_margin,
        );
    }
}
