<?php

/**
 * Coupons with the real M11 binding of the M7 contract: validity, limits, reservation at
 * placement, release on expiry, cancel or failure, redemption on payment, and admin management.
 * In-process; the races on limits are in CouponConcurrencyTest.
 */

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Catalog\Models\Category;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Payments\Models\Payment;
use App\Domains\Pricing\Models\PricingRule;
use App\Domains\Promotions\Contracts\CheckoutCoupons;
use App\Domains\Promotions\DTOs\CouponLookup;
use App\Domains\Promotions\Enums\CouponRedemptionStatus;
use App\Domains\Promotions\Enums\CouponType;
use App\Domains\Promotions\Exceptions\CouponInvalid;
use App\Domains\Promotions\Models\Coupon;
use App\Domains\Promotions\Models\CouponRedemption;
use App\Domains\Promotions\Services\DatabaseCheckoutCoupons;
use App\Domains\Promotions\Services\UnavailableCheckoutCoupons;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Database\QueryException;
use Illuminate\Testing\TestResponse;

beforeEach(function (): void {
    PricingRule::factory()->create(['percent_markup' => '50', 'is_active' => true]);
    $this->user = User::factory()->create();
    $this->variant = sellableVariant('10');
});

function couponCart(User $user, $variant, int $quantity = 1): void
{
    test()->actingAs($user)->postJson('/api/v1/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => $quantity])->assertCreated();
}

function applyRealCoupon(User $user, string $code): TestResponse
{
    return test()->actingAs($user)->postJson('/api/v1/storefront/cart/coupon', ['code' => $code]);
}

function placeCouponOrder(User $user, string $expectedTotal, string $key = 'coupon-order-0001'): TestResponse
{
    return test()->actingAs($user)->postJson('/api/v1/checkout/place-order', placeOrderBody($expectedTotal), ['Idempotency-Key' => $key]);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function couponNamed(string $code, array $attributes = []): Coupon
{
    return Coupon::factory()->create(['code' => $code, 'type' => CouponType::Fixed, 'value' => '1', 'usage_limit_per_user' => null, ...$attributes]);
}

it('is bound as the real coupon contract, with the fail-closed implementation still available', function (): void {
    expect(app(CheckoutCoupons::class))->toBeInstanceOf(DatabaseCheckoutCoupons::class)
        ->and(new UnavailableCheckoutCoupons)->toBeInstanceOf(CheckoutCoupons::class);
});

describe('validity', function (): void {
    it('applies a valid code, ignoring case, and discounts the cart', function (): void {
        couponNamed('SAVE1');
        couponCart($this->user, $this->variant);

        applyRealCoupon($this->user, ' save1 ')->assertOk()
            ->assertJsonPath('data.cart.coupon.code', 'SAVE1')
            ->assertJsonPath('data.cart.coupon.applied', true)
            ->assertJsonPath('data.cart.totals.discount.amount', '1.00')
            ->assertJsonPath('data.cart.totals.total.amount', '14.00');
    });

    it('applies a percentage capped by max_discount_base', function (): void {
        couponNamed('TENPC', ['type' => CouponType::Percent, 'value' => '10', 'max_discount_base' => '1.20']);
        couponCart($this->user, $this->variant, 2);

        applyRealCoupon($this->user, 'TENPC')->assertOk()->assertJsonPath('data.cart.totals.discount.amount', '1.20');
    });

    it('refuses a code that cannot be used now', function (array $attributes, string $message): void {
        couponNamed('NOPE', $attributes);
        couponCart($this->user, $this->variant);

        applyRealCoupon($this->user, 'NOPE')->assertUnprocessable()->assertJsonPath('code', 'coupon_invalid')->assertJsonPath('message', $message);
        expect(Cart::query()->sole()->coupon_id)->toBeNull();
    })->with([
        'inactive' => [['is_active' => false], 'This coupon cannot be used.'],
        'not started' => [['starts_at' => now()->addDay()], 'This coupon cannot be used.'],
        'expired' => [['ends_at' => now()->subMinute()], 'This coupon has expired.'],
        'below minimum order' => [['min_order_base' => '15.01'], 'Your order is below the minimum for this coupon.'],
        'global limit reached' => [['usage_limit' => 3, 'used_count' => 3], 'This coupon has reached its usage limit.'],
    ]);

    it('refuses unknown and deleted codes alike', function (): void {
        couponNamed('GONE')->delete();
        couponCart($this->user, $this->variant);

        applyRealCoupon($this->user, 'GONE')->assertUnprocessable()->assertJsonPath('code', 'coupon_invalid');
        applyRealCoupon($this->user, 'NEVER-EXISTED')->assertUnprocessable()->assertJsonPath('code', 'coupon_invalid');
    });

    it('refuses a customer who already holds their per-user uses, by account or by email', function (): void {
        $coupon = couponNamed('ONCE', ['usage_limit_per_user' => 1]);
        $earlier = Order::factory()->create(['status' => OrderStatus::Completed]);
        (new CouponRedemption)->forceFill(['coupon_id' => $coupon->id, 'order_id' => $earlier->id, 'user_id' => null, 'email_hash' => DatabaseCheckoutCoupons::emailHash(strtoupper($this->user->email)), 'discount_base' => '1', 'status' => CouponRedemptionStatus::Redeemed])->save();
        couponCart($this->user, $this->variant);

        applyRealCoupon($this->user, 'ONCE')->assertOk()->assertJsonPath('data.cart.coupon.applied', true);

        expect(fn () => app(CheckoutCoupons::class)->resolve(new CouponLookup('ONCE', $this->user->id, $this->user->email, Money::of('15', 'USD'), [$this->variant->id], [$this->variant->product_id])))
            ->toThrow(CouponInvalid::class, 'already used');
    });

    it('does not count a released use against the buyer', function (): void {
        $coupon = couponNamed('BACK', ['usage_limit_per_user' => 1]);
        $earlier = Order::factory()->create(['status' => OrderStatus::Cancelled]);
        (new CouponRedemption)->forceFill(['coupon_id' => $coupon->id, 'order_id' => $earlier->id, 'user_id' => $this->user->id, 'discount_base' => '1', 'status' => CouponRedemptionStatus::Released])->save();
        couponCart($this->user, $this->variant);

        applyRealCoupon($this->user, 'BACK')->assertOk();
    });

    it('limits first-order coupons to buyers without a paid order', function (): void {
        couponNamed('FIRST', ['first_order_only' => true]);
        couponCart($this->user, $this->variant);
        applyRealCoupon($this->user, 'FIRST')->assertOk();

        Order::factory()->create(['user_id' => $this->user->id, 'paid_at' => now(), 'status' => OrderStatus::Completed]);
        applyRealCoupon($this->user, 'FIRST')->assertUnprocessable()->assertJsonPath('message', 'This coupon is for a first order only.');
    });

    it('applies a restricted coupon to the eligible lines only and refuses it when none match', function (): void {
        $category = Category::factory()->create();
        $eligible = sellableVariant('10', product: ['primary_category_id' => $category->id]);
        couponNamed('CAT', ['type' => CouponType::Percent, 'value' => '10', 'applicable_to' => ['categories' => [$category->id]]]);
        couponNamed('OTHER', ['applicable_to' => ['products' => [999999]]]);
        couponCart($this->user, $this->variant);

        applyRealCoupon($this->user, 'CAT')->assertUnprocessable()->assertJsonPath('message', 'This coupon does not apply to the items in your cart.');

        couponCart($this->user, $eligible);
        applyRealCoupon($this->user, 'CAT')->assertOk()->assertJsonPath('data.cart.totals.discount.amount', '1.50');
        applyRealCoupon($this->user, 'OTHER')->assertUnprocessable();
    });

    it('converts a fixed coupon set in another currency to the base currency with the stored rate', function (): void {
        givenRate('EGP', '50');
        couponNamed('EGP50', ['value' => '50', 'currency' => 'EGP']);
        couponCart($this->user, $this->variant);

        applyRealCoupon($this->user, 'EGP50')->assertOk()->assertJsonPath('data.cart.totals.discount.amount', '1.00');
    });
});

describe('reservation, release and redemption', function (): void {
    beforeEach(function (): void {
        fakePayments();
    });

    it('reserves one use at placement and redeems it when the order is paid', function (): void {
        $coupon = couponNamed('SAVE1', ['usage_limit' => 5]);
        couponCart($this->user, $this->variant);
        applyRealCoupon($this->user, 'SAVE1')->assertOk();

        placeCouponOrder($this->user, '14.00')->assertCreated();

        $order = Order::query()->sole();
        $redemption = CouponRedemption::query()->sole();
        expect($coupon->fresh()->used_count)->toBe(1)
            ->and($order->coupon_id)->toBe($coupon->id)
            ->and($redemption->order_id)->toBe($order->id)
            ->and($redemption->user_id)->toBe($this->user->id)
            ->and($redemption->email_hash)->toBe(DatabaseCheckoutCoupons::emailHash($this->user->email))
            ->and($redemption->discount_base)->toBe('1.000000')
            ->and($redemption->status)->toBe(CouponRedemptionStatus::Reserved);

        $payment = Payment::factory()->create(['order_id' => $order->id]);
        Order::query()->whereKey($order->id)->update(['primary_payment_id' => $payment->id]);
        app(OrderStateMachine::class)->transition($order, OrderStatus::Paid, OrderActor::webhook());

        expect($redemption->fresh()->status)->toBe(CouponRedemptionStatus::Redeemed)
            ->and($coupon->fresh()->used_count)->toBe(1);
    });

    it('refuses placement when the last use was taken after the coupon was applied, and creates nothing', function (): void {
        $coupon = couponNamed('LAST', ['usage_limit' => 1]);
        couponCart($this->user, $this->variant);
        applyRealCoupon($this->user, 'LAST')->assertOk();
        $coupon->forceFill(['used_count' => 1])->save();

        placeCouponOrder($this->user, '14.00')->assertUnprocessable()->assertJsonPath('code', 'coupon_invalid');

        expect(Order::query()->count())->toBe(0)->and(CouponRedemption::query()->count())->toBe(0);
    });

    it('refuses a second order by the same customer beyond the per-user limit', function (): void {
        couponNamed('ONCE', ['usage_limit_per_user' => 1]);
        couponCart($this->user, $this->variant);
        applyRealCoupon($this->user, 'ONCE')->assertOk();
        placeCouponOrder($this->user, '14.00')->assertCreated();

        couponCart($this->user, sellableVariant('20'));
        applyRealCoupon($this->user, 'ONCE')->assertUnprocessable()->assertJsonPath('message', 'You have already used this coupon.');
        expect(Coupon::query()->sole()->used_count)->toBe(1);
    });

    it('releases the use when the unpaid order expires, so it can be used again', function (): void {
        $coupon = couponNamed('SAVE1', ['usage_limit' => 1]);
        couponCart($this->user, $this->variant);
        applyRealCoupon($this->user, 'SAVE1')->assertOk();
        placeCouponOrder($this->user, '14.00')->assertCreated();

        $this->travel(2)->hours();
        $this->artisan('orders:expire-unpaid')->assertSuccessful();

        expect(Order::query()->sole()->status)->toBe(OrderStatus::Cancelled)
            ->and(CouponRedemption::query()->sole()->status)->toBe(CouponRedemptionStatus::Released)
            ->and($coupon->fresh()->used_count)->toBe(0);

        couponCart(User::factory()->create(), $this->variant);
        expect(app(CheckoutCoupons::class)->resolve(new CouponLookup('SAVE1', null, 'x@example.test', Money::of('15', 'USD'), [], []))->couponId)->toBe($coupon->id);
    });

    it('releases the use when an unpaid order fails or is cancelled from review, and only once', function (OrderStatus $via, OrderStatus $to): void {
        $coupon = couponNamed('SAVE1', ['usage_limit' => 2]);
        couponCart($this->user, $this->variant);
        applyRealCoupon($this->user, 'SAVE1')->assertOk();
        placeCouponOrder($this->user, '14.00')->assertCreated();
        $order = Order::query()->sole();
        $machine = app(OrderStateMachine::class);

        if ($via !== OrderStatus::PendingPayment) {
            $machine->transition($order, $via, OrderActor::system());
        }

        $actor = $to === OrderStatus::Cancelled && $via === OrderStatus::ManualReview ? OrderActor::staff(actingAsStaffWith(['orders.review'])) : OrderActor::system();
        $machine->transition($order, $to, $actor);
        app(CheckoutCoupons::class)->release($order->id);

        expect(CouponRedemption::query()->sole()->status)->toBe(CouponRedemptionStatus::Released)
            ->and($coupon->fresh()->used_count)->toBe(0);
    })->with([
        'failed' => [OrderStatus::PendingPayment, OrderStatus::Failed],
        'cancelled' => [OrderStatus::PendingPayment, OrderStatus::Cancelled],
        'cancelled from manual review' => [OrderStatus::ManualReview, OrderStatus::Cancelled],
    ]);

    it('never releases a use once the order was paid', function (): void {
        $coupon = couponNamed('SAVE1');
        couponCart($this->user, $this->variant);
        applyRealCoupon($this->user, 'SAVE1')->assertOk();
        placeCouponOrder($this->user, '14.00')->assertCreated();
        $order = Order::query()->sole();
        $payment = Payment::factory()->create(['order_id' => $order->id]);
        Order::query()->whereKey($order->id)->update(['primary_payment_id' => $payment->id]);
        app(OrderStateMachine::class)->transition($order, OrderStatus::Paid, OrderActor::webhook());

        app(CheckoutCoupons::class)->release($order->id);

        expect(CouponRedemption::query()->sole()->status)->toBe(CouponRedemptionStatus::Redeemed)
            ->and($coupon->fresh()->used_count)->toBe(1);
    });

    it('has a database check that stops used_count passing the global limit', function (): void {
        $coupon = couponNamed('CAP', ['usage_limit' => 1, 'used_count' => 1]);

        expect(fn () => Coupon::query()->whereKey($coupon->id)->increment('used_count'))->toThrow(QueryException::class);
    });
});

describe('admin', function (): void {
    it('creates, lists, updates and deletes coupons with marketing.coupons, audited', function (): void {
        actingAsStaffWith(['marketing.coupons']);

        $id = $this->postJson('/admin/api/coupons', ['code' => 'spring-25', 'type' => 'percent', 'value' => '25', 'usage_limit' => 100, 'is_active' => true, 'used_count' => 50])
            ->assertCreated()
            ->assertJsonPath('data.code', 'SPRING-25')
            ->assertJsonPath('data.used_count', 0)
            ->assertJsonPath('data.created_by_type', 'staff')
            ->json('data.id');

        $this->getJson('/admin/api/coupons?q=SPRING')->assertOk()->assertJsonCount(1, 'data');
        $this->patchJson("/admin/api/coupons/{$id}", ['usage_limit' => 10])->assertOk()->assertJsonPath('data.usage_limit', 10);
        $this->deleteJson("/admin/api/coupons/{$id}")->assertOk();

        expect(Coupon::withTrashed()->find($id)->trashed())->toBeTrue()
            ->and(AuditLog::query()->where('action', 'like', 'promotions.coupon.%')->orderBy('id')->pluck('action')->all())->toBe(['promotions.coupon.created', 'promotions.coupon.updated', 'promotions.coupon.deleted']);
    });

    it('validates coupon input', function (array $body, string $field): void {
        actingAsStaffWith(['marketing.coupons']);
        couponNamed('TAKEN');

        $this->postJson('/admin/api/coupons', ['code' => 'GOOD', 'type' => 'fixed', 'value' => '5', ...$body])->assertUnprocessable()->assertJsonValidationErrors($field);
    })->with([
        'duplicate code' => [['code' => 'TAKEN'], 'code'],
        'percent above 100' => [['type' => 'percent', 'value' => '100.5'], 'value'],
        'number value' => [['value' => 5], 'value'],
        'currency on a percent' => [['type' => 'percent', 'value' => '5', 'currency' => 'USD'], 'currency'],
        'unknown product' => [['applicable_to' => ['products' => [999999]]], 'applicable_to.products.0'],
        'unknown restriction key' => [['applicable_to' => ['brands' => [1]]], 'applicable_to'],
    ]);

    it('refuses to lower the global limit below the uses already held', function (): void {
        actingAsStaffWith(['marketing.coupons']);
        $coupon = couponNamed('HELD', ['usage_limit' => 10, 'used_count' => 4]);

        $this->patchJson("/admin/api/coupons/{$coupon->id}", ['usage_limit' => 3])->assertUnprocessable()->assertJsonValidationErrors('usage_limit');
    });

    it('needs marketing.coupons', function (): void {
        actingAsStaffWith(['marketing.promotions']);

        $this->getJson('/admin/api/coupons')->assertForbidden();
        $this->postJson('/admin/api/coupons', ['code' => 'X', 'type' => 'fixed', 'value' => '1'])->assertForbidden();
    });
});
