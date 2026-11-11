<?php

namespace App\Domains\Orders\Services;

use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Identity\Models\User;
use App\Domains\Mcp\Services\PiiMasker;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\Refund;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus;
use App\Domains\Suppliers\Models\SupplierTransaction;

/**
 * The admin order page (M14, spec Phase 9.4, Figma "Admin Order Details"). One read with every
 * relation eager loaded; money is formatted here. Never contains a code, PIN, redemption URL,
 * delivery value, credential or provider secret: units show their masked code only.
 */
class AdminOrderReader
{
    public function __construct(
        private CurrencyConverter $converter,
        private OrderStateMachine $machine,
        private PiiMasker $masker,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function detail(Order $order, User $viewer): array
    {
        $order->load([
            'user:id,name,email,status',
            'paymentGateway:id,name',
            'riskAssessment',
            'orderItems:id,order_id,name_snapshot,sku_snapshot,delivery_type,quantity,unit_price_base,line_total_base',
            'orderItemUnits' => static fn ($query) => $query->orderBy('id'),
            'orderItemUnits.digitalCode:id,order_item_unit_id,masked_code',
            'orderItemUnits.supplierTransactions' => static fn ($query) => $query->orderBy('attempt_no'),
            'orderItemUnits.supplierTransactions.supplier:id,name',
            'orderItemUnits.refunds:refunds.id',
            'payments' => static fn ($query) => $query->orderBy('attempt_no'),
            'payments.paymentGateway:id,name',
            'refunds' => static fn ($query) => $query->orderByDesc('id'),
            'orderEvents' => static fn ($query) => $query->orderBy('id'),
        ]);

        $pii = $viewer->can('customers.view_pii');
        $email = $order->contact_email ?? $order->user?->email;
        $staff = OrderActor::staff($viewer);
        $openAttempts = $order->orderItemUnits->flatMap->supplierTransactions
            ->filter(static fn (SupplierTransaction $attempt): bool => in_array($attempt->status, [SupplierTransactionStatus::PendingVerification, SupplierTransactionStatus::Accepted], true));
        $items = $order->orderItems->keyBy('id');

        return [
            'public_id' => $order->public_id,
            'number' => $order->order_number,
            'status' => $order->status->value,
            'placed_at' => $order->created_at,
            'paid_at' => $order->paid_at,
            'customer' => [
                'name' => $order->customer_name ?? $order->user?->name,
                'email' => $pii ? $email : $this->masker->email($email),
                'registered' => $order->user_id !== null,
                'country' => $order->country_code,
                'ip_country' => $order->ip_country,
            ],
            'snapshot' => [
                'display_total' => $this->money($order->display_total, $order->display_currency),
                'payment_total' => $this->money($order->payment_total, $order->payment_currency),
                'payment_currency' => $order->payment_currency,
                'base_total' => $this->money($order->total_base, $order->base_currency),
                'discount' => $this->money($order->discount_base, $order->base_currency),
                'gateway' => $order->paymentGateway?->name,
                'rate' => $order->payment_rate === null ? null : rtrim(rtrim((string) $order->payment_rate, '0'), '.'),
                'rate_source' => $order->rate_source,
                'coupon' => $order->coupon_code,
            ],
            'risk' => $order->riskAssessment === null ? null : [
                'score' => $order->riskAssessment->score,
                'decision' => $order->riskAssessment->decision->value,
                'signals' => array_map(static fn (array $signal): string => (string) ($signal['code'] ?? ''), $order->riskAssessment->signals ?? []),
                'reviewed_at' => $order->riskAssessment->reviewed_at,
            ],
            'units' => $order->orderItemUnits->map(function (OrderItemUnit $unit) use ($items): array {
                $item = $items->get($unit->order_item_id);
                $last = $unit->supplierTransactions->last();

                return [
                    'public_id' => $unit->public_id,
                    'product' => $item?->name_snapshot,
                    'sku' => $item?->sku_snapshot,
                    'status' => $unit->status->value,
                    'masked_code' => $unit->digitalCode?->masked_code,
                    'supplier' => $last?->supplier?->name,
                    'reference' => $last?->client_reference,
                    'attempt_status' => $last?->status?->value,
                    'refunded' => $unit->refunds->isNotEmpty(),
                    'retryable' => in_array($unit->status, [OrderItemUnitStatus::Failed, OrderItemUnitStatus::ManualReview], true)
                        && ($last === null || in_array($last->status, [SupplierTransactionStatus::Rejected, SupplierTransactionStatus::NotAcceptedVerified], true)),
                    'refundable' => in_array($unit->status, [OrderItemUnitStatus::Failed, OrderItemUnitStatus::Delivered], true) && $unit->refunds->isEmpty(),
                ];
            })->all(),
            'attempts' => $order->orderItemUnits->flatMap(fn (OrderItemUnit $unit) => $unit->supplierTransactions->map(fn (SupplierTransaction $attempt): array => [
                'public_id' => $attempt->public_id,
                'unit' => $unit->public_id,
                'supplier' => $attempt->supplier?->name,
                'attempt_no' => $attempt->attempt_no,
                'status' => $attempt->status->value,
                'reference' => $attempt->client_reference,
                'is_fallback' => $attempt->is_fallback,
                'outcome' => $attempt->outcome_reason,
                'cost' => $this->money($attempt->actual_cost, $attempt->actual_cost_currency),
                'submitted_at' => $attempt->submitted_at,
                'next_check_at' => $attempt->next_verification_at,
                'deadline_at' => $attempt->verification_deadline_at,
                'unresolved' => in_array($attempt->status, [SupplierTransactionStatus::PendingVerification, SupplierTransactionStatus::Accepted, SupplierTransactionStatus::Submitting], true),
            ]))->values()->all(),
            'payments' => $order->payments->map(fn (Payment $payment): array => [
                'public_id' => $payment->public_id,
                'gateway' => $payment->paymentGateway?->name,
                'attempt_no' => $payment->attempt_no,
                'status' => $payment->status->value,
                'amount' => $this->money($payment->amount, $payment->currency),
                'reference' => $payment->provider_payment_id ?? $payment->provider_order_id,
                'failure' => $payment->failure_code,
                'primary' => $payment->id === $order->primary_payment_id,
                'created_at' => $payment->created_at,
                'verified_at' => $payment->verified_at,
            ])->all(),
            'refunds' => $order->refunds->map(fn (Refund $refund): array => [
                'public_id' => $refund->public_id,
                'status' => $refund->status->value,
                'reason' => $refund->reason->value,
                'destination' => $refund->destination->value,
                'amount' => $this->money($refund->amount, $refund->currency),
                'created_at' => $refund->created_at,
            ])->all(),
            'events' => $order->orderEvents->map(static fn (OrderEvent $event): array => [
                'type' => $event->type,
                'from' => $event->from_status?->value,
                'to' => $event->to_status?->value,
                'actor' => $event->actor_type?->value,
                'message' => $event->message,
                'at' => $event->created_at,
            ])->all(),
            'open_attempts' => $openAttempts->count(),
            'actions' => [
                'reconcile' => $viewer->can('orders.reconcile') && $openAttempts->isNotEmpty(),
                'approve' => $order->status === OrderStatus::ManualReview && $this->machine->canTransition($order, OrderStatus::Processing, $staff),
                'reject' => $order->status === OrderStatus::ManualReview && $viewer->can('orders.review'),
                'retry' => $viewer->can('orders.review') && in_array($order->status, [OrderStatus::ManualReview, OrderStatus::PartiallyFulfilled], true),
                'mark_not_accepted' => $viewer->can('orders.reconcile'),
                'refund' => $viewer->can('refunds.create') && $order->primary_payment_id !== null
                    && ($order->status === OrderStatus::RefundPending || $this->machine->canTransition($order, OrderStatus::RefundPending, $staff)),
            ],
        ];
    }

    /**
     * Orders that need a person (navigation badge).
     *
     * @param  list<OrderStatus>  $statuses
     */
    public function countInStatuses(array $statuses): int
    {
        return Order::query()->whereIn('status', $statuses)->count();
    }

    private function money(mixed $amount, ?string $currency): ?string
    {
        if ($amount === null || $currency === null || $currency === '') {
            return null;
        }

        return $this->converter->present(Money::of((string) $amount, $currency))['formatted'];
    }
}
