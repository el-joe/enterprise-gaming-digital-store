<?php

namespace App\Domains\Payments\Services;

use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderEventActorType;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Orders\Services\UnitStatusRecorder;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Enums\RefundDestination;
use App\Domains\Payments\Enums\RefundReason;
use App\Domains\Payments\Enums\RefundRequestedByType;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Exceptions\RefundRefused;
use App\Domains\Payments\Jobs\ExecuteRefund;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\Refund;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Creates refunds (BD4). By default only undelivered value is refunded, automatically, by the
 * system: units that definitively failed. Anything else, including delivered units, is at the
 * discretion of staff holding `refunds.create`. Each unit is refunded at most once
 * (`uq_ru_unit`), and a refund never exceeds what remains refundable on the payment. Refunds go
 * back to the original method by default. Staff may send one to the customer's wallet instead
 * (BD3, M11): the amount is then credited as store credit and the provider is not called; only a
 * registered customer's order can be refunded that way.
 *
 * The refund row is committed as `requested` and sent to the provider afterwards, outside the
 * transaction, by ExecuteRefund.
 */
class RefundService
{
    private const array OPEN = [RefundStatus::Requested, RefundStatus::Pending, RefundStatus::Unknown];

    public function __construct(
        private OrderStateMachine $machine,
        private UnitStatusRecorder $units,
        private PaymentGatewayRegistry $registry,
        private CurrencyConverter $converter,
    ) {}

    /**
     * Refund every failed unit of an order that is not already refunded. Used by the system
     * when an order enters `refund_pending`. Returns null when nothing is left to refund.
     */
    public function refundUndelivered(int $orderId): ?Refund
    {
        return DB::transaction(function () use ($orderId): ?Refund {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);

            $unitIds = OrderItemUnit::query()->where('order_id', $order->id)
                ->where('status', OrderItemUnitStatus::Failed)
                ->whereDoesntHave('refunds')
                ->pluck('public_id')->all();

            if ($unitIds === [] || $order->primary_payment_id === null) {
                return null;
            }

            return $this->create($order, $unitIds, RefundReason::Undelivered, OrderActor::system(), 'Automatic refund of undelivered units.');
        }, 3);
    }

    /**
     * Staff refund of chosen units (admin discretion). The order enters `refund_pending`
     * through the state machine, which checks `refunds.create`.
     *
     * @param  list<string>  $unitPublicIds
     */
    public function refundUnits(Order $order, array $unitPublicIds, RefundReason $reason, OrderActor $actor, ?string $note = null, RefundDestination $destination = RefundDestination::OriginalMethod): Refund
    {
        return DB::transaction(function () use ($order, $unitPublicIds, $reason, $actor, $note, $destination): Refund {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($locked->primary_payment_id === null) {
                throw new RefundRefused('This order has no verified payment to refund.', 'refund_not_allowed', 409);
            }

            if ($destination === RefundDestination::Wallet && $locked->user_id === null) {
                throw new RefundRefused('Only a registered customer can be refunded to a wallet.', 'refund_destination_unavailable');
            }

            if ($locked->status !== OrderStatus::RefundPending) {
                if (! $this->machine->canTransition($locked, OrderStatus::RefundPending, $actor)) {
                    throw new RefundRefused("Refunds cannot be created while the order is {$locked->status->value}.", 'refund_not_allowed', 409);
                }

                $this->machine->transition($locked, OrderStatus::RefundPending, $actor, 'Refund requested by staff.', ['reason' => $reason->value]);
            }

            return $this->create($locked, $unitPublicIds, $reason, $actor, $note, $destination);
        }, 3);
    }

    /**
     * Full refund of a second successful payment on the same order. Caller holds the order and
     * payment locks inside its transaction.
     */
    public function refundDuplicate(Payment $payment): Refund
    {
        $refund = (new Refund)->forceFill([
            'order_id' => $payment->order_id,
            'payment_id' => $payment->id,
            'destination' => RefundDestination::OriginalMethod,
            'status' => RefundStatus::Requested,
            'reason' => RefundReason::Duplicate,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'amount_base' => $payment->base_amount,
            'requested_by_type' => RefundRequestedByType::System,
            'note' => 'Duplicate capture on the same order.',
        ]);
        $refund->save();

        ExecuteRefund::dispatch($refund->id)->afterCommit();

        return $refund;
    }

    /**
     * @param  list<string>  $unitPublicIds
     */
    private function create(Order $order, array $unitPublicIds, RefundReason $reason, OrderActor $actor, ?string $note, RefundDestination $destination = RefundDestination::OriginalMethod): Refund
    {
        $payment = Payment::query()->lockForUpdate()->findOrFail($order->primary_payment_id);

        if (! in_array($payment->status, [PaymentStatus::Succeeded, PaymentStatus::PartiallyRefunded], true)) {
            throw new RefundRefused('The payment is not in a refundable state.', 'refund_not_allowed', 409);
        }

        /** @var Collection<int, OrderItemUnit> $units */
        $units = OrderItemUnit::query()->where('order_id', $order->id)->whereIn('public_id', array_values(array_unique($unitPublicIds)))
            ->lockForUpdate()->with('orderItem')->withCount('refunds')->get();

        if ($units->count() !== count(array_unique($unitPublicIds))) {
            throw new RefundRefused('One or more units do not belong to this order.', 'refund_units_invalid');
        }

        $allowed = $actor->type === OrderEventActorType::Staff
            ? [OrderItemUnitStatus::Failed, OrderItemUnitStatus::Delivered]
            : [OrderItemUnitStatus::Failed];

        foreach ($units as $unit) {
            if ((int) $unit->getAttribute('refunds_count') > 0) {
                throw new RefundRefused('A unit has already been refunded.', 'refund_duplicate', 409);
            }

            if (! in_array($unit->status, $allowed, true)) {
                throw new RefundRefused("A {$unit->status->value} unit cannot be refunded.", 'refund_units_invalid');
            }
        }

        if ($destination === RefundDestination::OriginalMethod) {
            $this->assertGatewayCanRefund($payment);
        }

        [$amount, $amountBase] = $this->amounts($order, $payment, $units);

        if (! $amount->isPositive()) {
            throw new RefundRefused('Nothing remains to refund on this payment.', 'refund_exceeds_payment', 409);
        }

        $refund = (new Refund)->forceFill([
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'destination' => $destination,
            'status' => RefundStatus::Requested,
            'reason' => $reason,
            'amount' => $amount->amount,
            'currency' => $amount->currency,
            'amount_base' => $amountBase->amount,
            'requested_by_type' => $actor->type === OrderEventActorType::Staff ? RefundRequestedByType::Staff : RefundRequestedByType::System,
            'requested_by_id' => $actor->id(),
            'approved_by' => $actor->id(),
            'note' => $note,
        ]);
        $refund->save();

        try {
            $refund->units()->attach($units->modelKeys(), ['created_at' => now(), 'updated_at' => now()]);
        } catch (UniqueConstraintViolationException) {
            throw new RefundRefused('A unit has already been refunded.', 'refund_duplicate', 409);
        }

        foreach ($units as $unit) {
            $this->units->move($unit, OrderItemUnitStatus::RefundPending, 'refund_requested', 'Refund requested.', ['refund_id' => $refund->id], $actor);
        }

        ExecuteRefund::dispatch($refund->id)->afterCommit();

        return $refund;
    }

    /**
     * The units' share of what was charged, in the payment currency, capped at what remains.
     * When the units are every unit not yet refunded, the remainder is refunded exactly, so
     * rounding never leaves a cent behind or refunds one too many.
     *
     * @param  Collection<int, OrderItemUnit>  $units
     * @return array{0: Money, 1: Money}
     */
    private function amounts(Order $order, Payment $payment, Collection $units): array
    {
        $open = Refund::query()->where('payment_id', $payment->id)->where('status', '!=', RefundStatus::Failed);
        $remaining = Money::of($payment->amount, $payment->currency)->minus(Money::of((string) (clone $open)->sum('amount'), $payment->currency));
        $remainingBase = Money::of($payment->base_amount, $payment->base_currency)->minus(Money::of((string) (clone $open)->sum('amount_base'), $payment->base_currency));

        $unrefunded = OrderItemUnit::query()->where('order_id', $order->id)->whereDoesntHave('refunds')->count();

        if ($units->count() >= $unrefunded) {
            return [$remaining, $remainingBase];
        }

        $amount = Money::zero($payment->currency);
        $amountBase = Money::zero($payment->base_currency);

        foreach ($units as $unit) {
            $share = $this->unitShare($unit->orderItem, $unit->public_id);
            $amountBase = $amountBase->plus(Money::of($share['net_base'], $payment->base_currency));
            $amount = $amount->plus($order->display_currency === $payment->currency
                ? Money::of($share['net_display'], $payment->currency)
                : $this->converter->roundForCurrency(Money::of($share['net_base'], $payment->base_currency)->convertedTo($payment->currency, $payment->rate)));
        }

        return [
            $amount->compareTo($remaining) > 0 ? $remaining : $amount,
            $amountBase->compareTo($remainingBase) > 0 ? $remainingBase : $amountBase,
        ];
    }

    /**
     * @return array{net_base: string, net_display: string}
     */
    private function unitShare(OrderItem $item, string $unitPublicId): array
    {
        foreach ($item->pricing_breakdown['units'] ?? [] as $entry) {
            if (($entry['unit_public_id'] ?? null) === $unitPublicId) {
                return ['net_base' => (string) $entry['net_base'], 'net_display' => (string) $entry['net_display']];
            }
        }

        throw new RefundRefused('The unit has no price record; refund it manually.', 'refund_units_invalid');
    }

    private function assertGatewayCanRefund(Payment $payment): void
    {
        $capabilities = $this->registry->forPayment($payment)->capabilities();

        if (! $capabilities->refunds) {
            throw new RefundRefused('This payment method does not support refunds.', 'refund_not_supported');
        }

        if ($capabilities->singleRefundInFlight && Refund::query()->where('payment_id', $payment->id)->whereIn('status', self::OPEN)->exists()) {
            throw new RefundRefused('Another refund on this payment is still in progress.', 'refund_in_progress', 409);
        }
    }
}
