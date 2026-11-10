<?php

use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderEventActorType;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Events\OrderStatusChanged;
use App\Domains\Orders\Exceptions\IllegalOrderTransition;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Orders\Services\OrderTransitionTable;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\Payment;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

/**
 * @param  array<string, mixed>  $attributes
 */
function orderInStatus(OrderStatus $status, bool $withVerifiedPayment = true, array $attributes = []): Order
{
    $order = Order::factory()->create(['status' => $status, 'placement_hash' => hash('sha256', Str::random(16)), ...$attributes]);

    if ($withVerifiedPayment) {
        $payment = Payment::factory()->create(['order_id' => $order->id, 'status' => PaymentStatus::Succeeded]);
        $order->forceFill(['primary_payment_id' => $payment->id])->save();
    }

    return $order->fresh();
}

/**
 * The first actor a table row names; staff rows get a staff member holding the row's permission.
 */
function actorForRow(OrderStatus $from, OrderStatus $to): OrderActor
{
    $rule = (new OrderTransitionTable)->rule($from, $to);
    $type = $rule['actors'][0];

    if ($type !== OrderEventActorType::Staff) {
        return OrderActor::of($type);
    }

    $staff = User::factory()->staff()->create();

    if ($rule['permission'] !== null) {
        Permission::findOrCreate($rule['permission']->value, 'web');
        $staff->givePermissionTo($rule['permission']->value);
    }

    return OrderActor::staff($staff);
}

/**
 * @return list<array{0: OrderStatus, 1: OrderStatus}>
 */
function illegalPairs(): array
{
    $allowed = array_map(static fn (array $pair): string => $pair[0]->value.'>'.$pair[1]->value, (new OrderTransitionTable)->pairs());
    $illegal = [];

    foreach (OrderStatus::cases() as $from) {
        foreach (OrderStatus::cases() as $to) {
            if (! in_array($from->value.'>'.$to->value, $allowed, true)) {
                $illegal["{$from->value} to {$to->value}"] = [$from, $to];
            }
        }
    }

    return $illegal;
}

it('performs every row of the transition table and records an event', function (OrderStatus $from, OrderStatus $to): void {
    Event::fake([OrderStatusChanged::class]);
    $order = orderInStatus($from);
    $actor = actorForRow($from, $to);

    $result = app(OrderStateMachine::class)->transition($order, $to, $actor, 'test');

    expect($result->status)->toBe($to)
        ->and($result->status_changed_at)->not->toBeNull();

    $event = OrderEvent::query()->where('order_id', $order->id)->sole();
    expect($event->type)->toBe('status_changed')
        ->and($event->from_status)->toBe($from)
        ->and($event->to_status)->toBe($to)
        ->and($event->actor_type)->toBe($actor->type)
        ->and($event->actor_id)->toBe($actor->id());

    Event::assertDispatched(OrderStatusChanged::class, static fn (OrderStatusChanged $changed): bool => $changed->orderId === $order->id && $changed->from === $from && $changed->to === $to);
})->with(fn (): array => (new OrderTransitionTable)->pairs());

it('refuses every transition the table does not list, changing nothing', function (OrderStatus $from, OrderStatus $to): void {
    $order = orderInStatus($from);

    expect(fn () => app(OrderStateMachine::class)->transition($order, $to, OrderActor::system()))
        ->toThrow(IllegalOrderTransition::class);

    expect($order->fresh()->status)->toBe($from)
        ->and(OrderEvent::query()->where('order_id', $order->id)->exists())->toBeFalse();
})->with(fn (): array => illegalPairs());

it('refuses an actor the row does not name', function (OrderStatus $from, OrderStatus $to): void {
    $order = orderInStatus($from);

    expect(fn () => app(OrderStateMachine::class)->transition($order, $to, OrderActor::of(OrderEventActorType::Mcp)))
        ->toThrow(IllegalOrderTransition::class, 'mcp');

    expect($order->fresh()->status)->toBe($from);
})->with(fn (): array => (new OrderTransitionTable)->pairs());

it('refuses staff rows to staff without the permission and to customers', function (OrderStatus $from, OrderStatus $to): void {
    $order = orderInStatus($from);
    $machine = app(OrderStateMachine::class);

    expect(fn () => $machine->transition($order, $to, OrderActor::staff(User::factory()->staff()->create())))->toThrow(IllegalOrderTransition::class)
        ->and(fn () => $machine->transition($order, $to, OrderActor::staff(User::factory()->create())))->toThrow(IllegalOrderTransition::class)
        ->and($order->fresh()->status)->toBe($from);
})->with([
    'manual_review to processing' => [OrderStatus::ManualReview, OrderStatus::Processing],
    'manual_review to completed' => [OrderStatus::ManualReview, OrderStatus::Completed],
    'manual_review to refund_pending' => [OrderStatus::ManualReview, OrderStatus::RefundPending],
    'manual_review to cancelled' => [OrderStatus::ManualReview, OrderStatus::Cancelled],
    'partially_fulfilled to processing' => [OrderStatus::PartiallyFulfilled, OrderStatus::Processing],
    'completed to refund_pending' => [OrderStatus::Completed, OrderStatus::RefundPending],
    'partially_refunded to refund_pending' => [OrderStatus::PartiallyRefunded, OrderStatus::RefundPending],
    'pending_verification to processing' => [OrderStatus::PendingVerification, OrderStatus::Processing],
]);

it('never moves an order without a verified payment into paid or fulfilment (rule 9)', function (OrderStatus $from, OrderStatus $to): void {
    $order = orderInStatus($from, withVerifiedPayment: false);

    expect(fn () => app(OrderStateMachine::class)->transition($order, $to, actorForRow($from, $to)))
        ->toThrow(IllegalOrderTransition::class, 'verified payment');

    expect($order->fresh()->status)->toBe($from);
})->with(fn (): array => array_values(array_filter((new OrderTransitionTable)->pairs(), static fn (array $pair): bool => $pair[1]->requiresVerifiedPayment())));

it('lets a customer cancel an unpaid order but never mark one paid', function (): void {
    $machine = app(OrderStateMachine::class);
    $order = orderInStatus(OrderStatus::PendingPayment);

    expect(fn () => $machine->transition($order, OrderStatus::Paid, OrderActor::customer()))->toThrow(IllegalOrderTransition::class)
        ->and($machine->transition($order, OrderStatus::Cancelled, OrderActor::customer())->status)->toBe(OrderStatus::Cancelled);
});

it('logs an alert for an illegal transition', function (): void {
    Log::shouldReceive('alert')->once()->withArgs(static fn (string $message, array $context): bool => $message === 'orders.illegal_transition' && $context['to'] === 'completed');

    expect(fn () => app(OrderStateMachine::class)->transition(orderInStatus(OrderStatus::PendingPayment), OrderStatus::Completed, OrderActor::system()))
        ->toThrow(IllegalOrderTransition::class);
});

it('stamps paid_at and completed_at and clears the placement hash on closed statuses', function (): void {
    $machine = app(OrderStateMachine::class);
    $order = orderInStatus(OrderStatus::PendingPayment);

    $paid = $machine->transition($order, OrderStatus::Paid, OrderActor::webhook());
    expect($paid->paid_at)->not->toBeNull()->and($paid->placement_hash)->not->toBeNull();

    $machine->transition($order, OrderStatus::Processing, OrderActor::system());
    $completed = $machine->transition($order, OrderStatus::Completed, OrderActor::system());

    expect($completed->completed_at)->not->toBeNull()
        ->and($completed->placement_hash)->toBeNull()
        ->and($completed->paid_at->equalTo($paid->paid_at))->toBeTrue();
});

it('releases the coupon reservation when an unpaid order is cancelled or fails', function (OrderStatus $to): void {
    $coupons = fakeCoupons();
    $order = orderInStatus(OrderStatus::PendingPayment);

    app(OrderStateMachine::class)->transition($order, $to, OrderActor::system());

    expect($coupons->released)->toBe([$order->id]);
})->with([OrderStatus::Cancelled, OrderStatus::Failed]);

it('refuses a status change saved through the model', function (): void {
    $order = orderInStatus(OrderStatus::PendingPayment);

    expect(fn () => $order->forceFill(['status' => OrderStatus::Paid])->save())->toThrow(IllegalOrderTransition::class)
        ->and($order->fresh()->status)->toBe(OrderStatus::PendingPayment);
});

it('has no code outside the state machine that writes order status', function (): void {
    $root = dirname(__DIR__, 3).'/app';
    $offenders = [];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
        if ($file->getExtension() !== 'php' || str_ends_with($file->getPathname(), 'Orders/Services/OrderStateMachine.php')) {
            continue;
        }

        $source = file_get_contents($file->getPathname());

        if (preg_match('/[\'"]status[\'"]\s*=>\s*OrderStatus::(?!class\b)|->status\s*=\s*OrderStatus::/', $source) === 1) {
            $offenders[] = $file->getPathname();
        }
    }

    expect($offenders)->toBe([]);
});
