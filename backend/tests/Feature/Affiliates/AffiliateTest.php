<?php

/**
 * Affiliate clicks, attribution at placement, commission accrual on completion and its reversal
 * or reduction on refund, and admin management.
 */

use App\Domains\Affiliates\Enums\AffiliateCommissionStatus;
use App\Domains\Affiliates\Enums\AffiliateReferralStatus;
use App\Domains\Affiliates\Enums\AffiliateStatus;
use App\Domains\Affiliates\Models\Affiliate;
use App\Domains\Affiliates\Models\AffiliateCommission;
use App\Domains\Affiliates\Models\AffiliateReferral;
use App\Domains\Affiliates\Services\AffiliateCommissionService;
use App\Domains\Affiliates\Services\AffiliateTracker;
use App\Domains\Audit\Models\AuditLog;
use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Models\Refund;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Queue::fake([FulfilOrder::class]);
    $this->affiliate = Affiliate::factory()->create(['code' => 'GAMER', 'status' => AffiliateStatus::Active, 'commission_pct' => '5.0000']);
});

/**
 * A paid order attributed to the affiliate, then completed.
 */
function completedAffiliateOrder(Affiliate $affiliate, string $total = '40', int $units = 2): Order
{
    $order = paidOrderFor(sellableVariant('10'), $units, unitPrice: bcdiv($total, (string) $units, 6), orderAttributes: ['user_id' => User::factory()->create()->id]);
    $referral = AffiliateReferral::factory()->create(['affiliate_id' => $affiliate->id, 'order_id' => $order->id]);
    Order::query()->whereKey($order->id)->update(['affiliate_referral_id' => $referral->id]);

    $machine = app(OrderStateMachine::class);
    $machine->transition($order, OrderStatus::Processing, OrderActor::system());
    setUnitStatuses($order, array_fill(0, $units, Unit::Delivered));

    return $machine->transition($order, OrderStatus::Completed, OrderActor::system());
}

function refundOrderBy(Order $order, string $amountBase, OrderStatus $to): void
{
    Refund::factory()->create(['order_id' => $order->id, 'payment_id' => $order->primary_payment_id, 'status' => RefundStatus::Succeeded, 'amount' => $amountBase, 'amount_base' => $amountBase]);
    $machine = app(OrderStateMachine::class);
    $machine->transition($order, OrderStatus::RefundPending, OrderActor::staff(actingAsStaffWith(['refunds.create'])));
    $machine->transition($order, $to, OrderActor::system());
}

describe('clicks and attribution', function (): void {
    it('records a click against a hashed token kept in an HTTP-only cookie', function (): void {
        $response = $this->postJson('/api/v1/storefront/affiliates/click', ['code' => 'gamer', 'landing_url' => '/steam'])->assertStatus(202);

        $cookie = collect($response->headers->getCookies())->firstWhere(fn ($cookie): bool => $cookie->getName() === 'aff_ref');
        $referral = AffiliateReferral::query()->sole();

        expect($cookie->isHttpOnly())->toBeTrue()
            ->and($referral->affiliate_id)->toBe($this->affiliate->id)
            ->and($referral->landing_url)->toBe('/steam')
            ->and($referral->status)->toBe(AffiliateReferralStatus::Clicked)
            ->and(strlen($referral->visitor_token_hash))->toBe(64);
    });

    it('answers the same for an unknown or suspended code and records nothing', function (string $code): void {
        Affiliate::factory()->create(['code' => 'PAUSED', 'status' => AffiliateStatus::Suspended]);

        $this->postJson('/api/v1/storefront/affiliates/click', ['code' => $code])->assertStatus(202)->assertJsonPath('message', 'Recorded.');
        expect(AffiliateReferral::query()->count())->toBe(0);
    })->with(['NOBODY', 'PAUSED']);

    it('attributes an order placed from the same browser to the last click', function (): void {
        fakePayments();
        $buyer = User::factory()->create();
        $token = str_repeat('a', 40);
        app(AffiliateTracker::class)->recordClick('GAMER', $token, null);
        $this->actingAs($buyer)->postJson('/api/v1/storefront/cart/items', ['variant_id' => sellableVariant('10')->public_id, 'quantity' => 1])->assertCreated();

        $this->withCookie('aff_ref', $token)->withCredentials()->actingAs($buyer)
            ->postJson('/api/v1/checkout/place-order', placeOrderBody('10.00'), ['Idempotency-Key' => 'aff-order-0001'])->assertCreated();

        $order = Order::query()->sole();
        $referral = AffiliateReferral::query()->sole();
        expect($order->affiliate_referral_id)->toBe($referral->id)->and($referral->order_id)->toBe($order->id);
    });

    it('never attributes a click outside the window or an affiliate buying for themselves', function (): void {
        $order = Order::factory()->create(['user_id' => $this->affiliate->user_id]);
        $other = Order::factory()->create();
        app(AffiliateTracker::class)->recordClick('GAMER', 'self-token', null);
        app(AffiliateTracker::class)->recordClick('GAMER', 'old-token', null);
        AffiliateReferral::query()->latest('id')->first()->forceFill(['clicked_at' => now()->subDays(31)])->save();

        expect(app(AffiliateTracker::class)->attribute($order->public_id, 'self-token'))->toBeFalse()
            ->and(app(AffiliateTracker::class)->attribute($other->public_id, 'old-token'))->toBeFalse()
            ->and(app(AffiliateTracker::class)->attribute($other->public_id, null))->toBeFalse()
            ->and(Order::query()->whereNotNull('affiliate_referral_id')->count())->toBe(0);
    });
});

describe('commission', function (): void {
    it('accrues a pending commission once when an attributed order completes', function (): void {
        $order = completedAffiliateOrder($this->affiliate, '40');

        $commission = AffiliateCommission::query()->sole();
        expect($commission->order_id)->toBe($order->id)
            ->and($commission->amount_base)->toBe('2.000000')
            ->and($commission->status)->toBe(AffiliateCommissionStatus::Pending)
            ->and(AffiliateReferral::query()->sole()->status)->toBe(AffiliateReferralStatus::Converted)
            ->and(app(AffiliateCommissionService::class)->accrue($order->id)->id)->toBe($commission->id)
            ->and(AffiliateCommission::query()->count())->toBe(1);
    });

    it('accrues nothing for an order without a referral or for a suspended affiliate', function (): void {
        $machine = app(OrderStateMachine::class);
        $order = paidOrderFor(sellableVariant('10'));
        $machine->transition($order, OrderStatus::Processing, OrderActor::system());
        setUnitStatuses($order, [Unit::Delivered]);
        $machine->transition($order, OrderStatus::Completed, OrderActor::system());

        $suspended = Affiliate::factory()->create(['status' => AffiliateStatus::Suspended]);
        completedAffiliateOrder($suspended);

        expect(AffiliateCommission::query()->count())->toBe(0)
            ->and(AffiliateReferral::query()->sole()->status)->toBe(AffiliateReferralStatus::Rejected);
    });

    it('reverses the commission when the order is refunded in full', function (): void {
        $order = completedAffiliateOrder($this->affiliate, '40');

        refundOrderBy($order, '40', OrderStatus::Refunded);

        expect(AffiliateCommission::query()->sole()->status)->toBe(AffiliateCommissionStatus::Reversed)
            ->and(AffiliateCommission::query()->sole()->amount_base)->toBe('2.000000');
    });

    it('reduces the commission to what the customer kept on a partial refund', function (): void {
        $order = completedAffiliateOrder($this->affiliate, '40');

        refundOrderBy($order, '20', OrderStatus::PartiallyRefunded);

        expect(AffiliateCommission::query()->sole()->status)->toBe(AffiliateCommissionStatus::Pending)
            ->and(AffiliateCommission::query()->sole()->amount_base)->toBe('1.000000');
    });

    it('leaves a paid-out commission unchanged on refund and alerts finance', function (): void {
        $order = completedAffiliateOrder($this->affiliate, '40');
        AffiliateCommission::query()->update(['status' => AffiliateCommissionStatus::Paid->value]);
        Log::spy();

        refundOrderBy($order, '40', OrderStatus::Refunded);

        expect(AffiliateCommission::query()->sole()->status)->toBe(AffiliateCommissionStatus::Paid);
        Log::shouldHaveReceived('alert')->withArgs(fn (string $message): bool => $message === 'affiliates.paid_commission_refunded')->once();
    });
});

describe('admin', function (): void {
    it('manages affiliates with write-only payout details', function (): void {
        actingAsStaffWith(['marketing.affiliates']);
        $user = User::factory()->create();

        $id = $this->postJson('/admin/api/affiliates', [
            'user_id' => $user->id, 'code' => 'streamer-1', 'status' => 'active', 'commission_pct' => '7.5',
            'payout_details' => ['method' => 'bank', 'account' => 'EG00 1234 SECRET'],
        ])->assertCreated()
            ->assertJsonPath('data.code', 'STREAMER-1')
            ->assertJsonPath('data.payout_details_configured', true)
            ->assertJsonMissingPath('data.payout_details')
            ->json('data.id');

        $this->getJson("/admin/api/affiliates/{$id}")->assertOk()->assertDontSee('SECRET');
        $this->patchJson("/admin/api/affiliates/{$id}", ['status' => 'suspended'])->assertOk()->assertJsonPath('data.status', 'suspended');
        $this->patchJson("/admin/api/affiliates/{$id}", ['user_id' => User::factory()->create()->id])->assertUnprocessable()->assertJsonValidationErrors('user_id');

        expect(Affiliate::query()->find($id)->payout_details)->toBe(['method' => 'bank', 'account' => 'EG00 1234 SECRET'])
            ->and(AuditLog::query()->where('action', 'affiliates.affiliate.created')->sole()->after)->not->toHaveKey('payout_details');
    });

    it('lists an affiliate\'s commissions and approves a pending one once', function (): void {
        actingAsStaffWith(['marketing.affiliates']);
        completedAffiliateOrder($this->affiliate);
        actingAsStaffWith(['marketing.affiliates']);
        $commission = AffiliateCommission::query()->sole();

        $this->getJson("/admin/api/affiliates/{$this->affiliate->id}/commissions")->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.amount_base', '2.000000');
        $this->postJson("/admin/api/affiliate-commissions/{$commission->id}/approve")->assertOk()->assertJsonPath('data.status', 'approved');
        $this->postJson("/admin/api/affiliate-commissions/{$commission->id}/approve")->assertStatus(409);
    });

    it('needs marketing.affiliates', function (): void {
        actingAsStaffWith(['marketing.coupons']);

        $this->getJson('/admin/api/affiliates')->assertForbidden();
    });
});
