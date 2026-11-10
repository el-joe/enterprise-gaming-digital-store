<?php

use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Enums\RiskAssessmentDecision;
use App\Domains\Orders\Exceptions\IllegalOrderTransition;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Models\RiskAssessment;
use App\Domains\Orders\Services\OrderExpiryService;
use App\Domains\Orders\Services\OrderRiskGate;
use App\Domains\Orders\Services\OrderStatusAggregator;
use App\Domains\Orders\Services\RiskEngine;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Services\SettingsService;
use Database\Factories\Shared\CountryFactory;

/**
 * @param  array<string, mixed>  $attributes
 */
function paidOrder(OrderStatus $status = OrderStatus::Paid, array $attributes = []): Order
{
    $order = Order::factory()->create(['status' => $status, 'paid_at' => now(), ...$attributes]);
    $payment = Payment::factory()->create(['order_id' => $order->id, 'status' => PaymentStatus::Succeeded]);
    $order->forceFill(['primary_payment_id' => $payment->id])->save();

    return $order->fresh();
}

/**
 * @param  list<Unit>  $statuses
 */
function withUnits(Order $order, array $statuses): Order
{
    $item = OrderItem::factory()->create(['order_id' => $order->id, 'quantity' => count($statuses)]);

    foreach ($statuses as $status) {
        OrderItemUnit::factory()->create(['order_id' => $order->id, 'order_item_id' => $item->id, 'status' => $status]);
    }

    return $order;
}

describe('aggregator', function (): void {
    it('derives the order status from units in the documented precedence', function (array $units, OrderStatus $expected): void {
        expect(app(OrderStatusAggregator::class)->derive($units))->toBe($expected);
    })->with([
        'any pending_verification wins' => [[Unit::Delivered, Unit::ManualReview, Unit::PendingVerification], OrderStatus::PendingVerification],
        'manual review' => [[Unit::Delivered, Unit::ManualReview, Unit::SupplierPending], OrderStatus::ManualReview],
        'manual task counts as review' => [[Unit::ManualTask, Unit::Queued], OrderStatus::ManualReview],
        'supplier pending' => [[Unit::Queued, Unit::SupplierPending, Unit::Delivered], OrderStatus::SupplierPending],
        'in flight without supplier' => [[Unit::Pending, Unit::Submitting, Unit::Delivered], OrderStatus::Processing],
        'all delivered' => [[Unit::Delivered, Unit::Delivered], OrderStatus::Completed],
        'some delivered' => [[Unit::Delivered, Unit::Failed], OrderStatus::PartiallyFulfilled],
        'none delivered' => [[Unit::Failed, Unit::Cancelled], OrderStatus::RefundPending],
    ]);

    it('refuses to derive a status without units', function (): void {
        app(OrderStatusAggregator::class)->derive([]);
    })->throws(InvalidArgumentException::class);

    it('applies the derived status through the state machine', function (): void {
        $order = withUnits(paidOrder(OrderStatus::Processing), [Unit::Delivered, Unit::Delivered]);

        expect(app(OrderStatusAggregator::class)->sync($order, OrderActor::system()))->toBe(OrderStatus::Completed)
            ->and($order->fresh()->status)->toBe(OrderStatus::Completed)
            ->and(OrderEvent::query()->where('order_id', $order->id)->where('to_status', 'completed')->exists())->toBeTrue();
    });

    it('does nothing when the order already has the derived status', function (): void {
        $order = withUnits(paidOrder(OrderStatus::Processing), [Unit::Queued]);

        expect(app(OrderStatusAggregator::class)->sync($order, OrderActor::system()))->toBeNull()
            ->and(OrderEvent::query()->where('order_id', $order->id)->exists())->toBeFalse();
    });

    it('throws when the derived move is not in the table', function (): void {
        $order = withUnits(Order::factory()->create(['status' => OrderStatus::PendingPayment]), [Unit::Delivered]);

        expect(fn () => app(OrderStatusAggregator::class)->sync($order, OrderActor::system()))->toThrow(IllegalOrderTransition::class)
            ->and($order->fresh()->status)->toBe(OrderStatus::PendingPayment);
    });
});

describe('expiry', function (): void {
    it('cancels unpaid orders past their window and leaves the rest', function (): void {
        $expired = Order::factory()->create(['status' => OrderStatus::PendingPayment, 'expires_at' => now()->subMinute()]);
        $current = Order::factory()->create(['status' => OrderStatus::PendingPayment, 'expires_at' => now()->addMinutes(5)]);
        $processing = Order::factory()->create(['status' => OrderStatus::PaymentProcessing, 'expires_at' => now()->subMinute()]);
        $coupons = fakeCoupons();

        $this->artisan('orders:expire-unpaid')->expectsOutput('Cancelled 1, skipped 0.')->assertSuccessful();

        expect($expired->fresh()->status)->toBe(OrderStatus::Cancelled)
            ->and($current->fresh()->status)->toBe(OrderStatus::PendingPayment)
            ->and($processing->fresh()->status)->toBe(OrderStatus::PaymentProcessing)
            ->and($coupons->released)->toBe([$expired->id])
            ->and(OrderEvent::query()->where('order_id', $expired->id)->sole()->metadata)->toBe(['reason' => 'quote_expired']);
    });

    it('is a no-op the second time', function (): void {
        Order::factory()->create(['status' => OrderStatus::PendingPayment, 'expires_at' => now()->subMinute()]);

        expect(app(OrderExpiryService::class)->expireDue())->toBe(['cancelled' => 1, 'skipped' => 0])
            ->and(app(OrderExpiryService::class)->expireDue())->toBe(['cancelled' => 0, 'skipped' => 0]);
    });

    describe('with another connection holding the order', function (): void {
        beforeEach(function (): void {
            commitTestTransaction();
            config(['database.connections.mysql_second' => config('database.connections.mysql')]);
        });

        afterEach(function (): void {
            DB::purge('mysql_second');

            foreach (['order_events', 'orders', 'currencies', 'countries'] as $table) {
                DB::table($table)->delete();
            }

            reopenTestTransaction();
        });

        it('skips an order locked by a payment in progress and cancels it on the next run', function (): void {
            $order = Order::factory()->create(['status' => OrderStatus::PendingPayment, 'expires_at' => now()->subMinute()]);

            $second = DB::connection('mysql_second');
            $second->beginTransaction();
            $second->select('select id from orders where id = ? for update', [$order->id]);

            expect(app(OrderExpiryService::class)->expireDue())->toBe(['cancelled' => 0, 'skipped' => 1])
                ->and($order->fresh()->status)->toBe(OrderStatus::PendingPayment);

            $second->rollBack();

            expect(app(OrderExpiryService::class)->expireDue())->toBe(['cancelled' => 1, 'skipped' => 0]);
        });
    });
});

describe('risk', function (): void {
    it('sends a clean paid order to processing and stores the assessment', function (): void {
        $order = paidOrder(attributes: ['total_base' => '20.000000', 'user_id' => User::factory()->create(['created_at' => now()->subYear()])->id]);

        expect(app(OrderRiskGate::class)->evaluate($order))->toBe(OrderStatus::Processing);

        $assessment = RiskAssessment::query()->where('order_id', $order->id)->sole();
        expect($assessment->decision)->toBe(RiskAssessmentDecision::Pass)
            ->and($assessment->score)->toBe(0)
            ->and($order->fresh()->risk_score)->toBe(0);
    });

    it('holds a risky paid order for manual review with the signals that fired', function (): void {
        $order = paidOrder(attributes: ['total_base' => '500.000000', 'contact_email' => 'x@mailinator.com', 'country_code' => CountryFactory::ensure('US'), 'ip_country' => 'EG']);

        expect(app(OrderRiskGate::class)->evaluate($order))->toBe(OrderStatus::ManualReview);

        $assessment = RiskAssessment::query()->where('order_id', $order->id)->sole();
        expect(array_column($assessment->signals, 'code'))->toEqualCanonicalizing(['guest_checkout', 'first_order_high_value', 'country_mismatch', 'disposable_email'])
            ->and($assessment->score)->toBe(75)
            ->and($assessment->decision)->toBe(RiskAssessmentDecision::Review);
    });

    it('marks a score at the block threshold as block and still only holds it', function (): void {
        app(SettingsService::class)->set('security', 'risk_block_score', '30', SettingType::Int);
        app(SettingsService::class)->set('security', 'risk_review_score', '10', SettingType::Int);
        $order = paidOrder(attributes: ['total_base' => '500.000000']);

        expect(app(OrderRiskGate::class)->evaluate($order))->toBe(OrderStatus::ManualReview)
            ->and(RiskAssessment::query()->where('order_id', $order->id)->sole()->decision)->toBe(RiskAssessmentDecision::Block);
    });

    it('counts velocity, failed attempts and card reuse', function (): void {
        $order = paidOrder(attributes: ['guest_email' => 'g@example.com', 'ip_address' => '203.0.113.9', 'total_base' => '5.000000']);
        Order::factory()->count(3)->create(['guest_email' => 'g@example.com', 'ip_address' => '203.0.113.9']);
        Order::factory()->count(2)->create(['ip_address' => '203.0.113.9']);
        Payment::factory()->count(3)->create(['order_id' => $order->id, 'status' => PaymentStatus::Failed, 'attempt_no' => fn () => fake()->unique()->numberBetween(2, 50)]);
        $order->primaryPayment->forceFill(['card_fingerprint_hash' => str_repeat('a', 64)])->save();
        foreach (Order::factory()->count(3)->create() as $other) {
            Payment::factory()->create(['order_id' => $other->id, 'card_fingerprint_hash' => str_repeat('a', 64)]);
        }

        $codes = array_column(app(RiskEngine::class)->score($order->fresh())['signals'], 'code');

        expect($codes)->toContain('buyer_velocity', 'ip_velocity', 'failed_payment_attempts', 'card_velocity');
    });

    it('assesses an order once', function (): void {
        $order = paidOrder();
        $first = app(RiskEngine::class)->assess($order);

        expect(app(RiskEngine::class)->assess($order)->id)->toBe($first->id)
            ->and(RiskAssessment::query()->where('order_id', $order->id)->count())->toBe(1);
    });

    it('refuses to run on an unpaid order', function (): void {
        expect(fn () => app(OrderRiskGate::class)->evaluate(Order::factory()->create(['status' => OrderStatus::PendingPayment])))
            ->toThrow(IllegalOrderTransition::class);
    });
});
