<?php

namespace App\Domains\Orders\Services;

use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Enums\RiskAssessmentDecision;
use App\Domains\Orders\Exceptions\IllegalOrderTransition;
use App\Domains\Orders\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Runs the risk engine on a paid order and moves it on: `pass` to processing, `review` and
 * `block` to manual_review. The table has no paid to refund_pending row, so a blocked order is
 * held for a human, who refunds it from manual_review (refunds are M10).
 */
class OrderRiskGate
{
    public function __construct(private RiskEngine $engine, private OrderStateMachine $machine) {}

    public function evaluate(Order|int $order): OrderStatus
    {
        $orderId = $order instanceof Order ? $order->id : $order;

        return DB::transaction(function () use ($orderId): OrderStatus {
            $locked = Order::query()->lockForUpdate()->findOrFail($orderId);

            if ($locked->status !== OrderStatus::Paid) {
                throw new IllegalOrderTransition("The risk gate only runs on paid orders, not {$locked->status->value}.");
            }

            $assessment = $this->engine->assess($locked);
            $target = $assessment->decision === RiskAssessmentDecision::Pass ? OrderStatus::Processing : OrderStatus::ManualReview;

            $this->machine->transition($locked, $target, OrderActor::system(), "Risk check: {$assessment->decision->value}.", [
                'risk_score' => $assessment->score,
                'risk_decision' => $assessment->decision->value,
            ]);

            return $target;
        });
    }
}
