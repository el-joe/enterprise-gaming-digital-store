<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Fulfillment\Enums\ManualFulfillmentTaskStatus as TaskStatus;
use App\Domains\Fulfillment\Models\ManualFulfillmentTask;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\OrderProgress;
use App\Domains\Orders\Services\UnitStatusRecorder;
use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Staff fulfilment of direct top-ups and manual products (BD2). Claim, complete and reject are
 * audited; completing marks the unit delivered and derives the order status with the staff
 * member as actor (manual_review to completed needs `orders.fulfill_manual`).
 */
class ManualTaskService
{
    public function __construct(
        private ListQueryApplier $lists,
        private UnitStatusRecorder $units,
        private OrderProgress $progress,
        private AuditLogger $audit,
    ) {}

    public function list(ListQuery $list): LengthAwarePaginator
    {
        return $this->lists->paginate(
            ManualFulfillmentTask::query()->with(['orderItemUnit.order:id,public_id,order_number,status', 'orderItemUnit.orderItem:id,name_snapshot,sku_snapshot,delivery_type']),
            $list,
            ['status' => 'exact', 'assigned_to' => 'exact'],
            ['id', 'created_at', 'status'],
            [],
            'id',
        );
    }

    public function forStaffView(ManualFulfillmentTask $task): ManualFulfillmentTask
    {
        return $task->load(['orderItemUnit.order:id,public_id,order_number,status', 'orderItemUnit.orderItem:id,name_snapshot,sku_snapshot,delivery_type', 'orderItemUnit.orderItem.orderItemDeliveryValues']);
    }

    public function claim(ManualFulfillmentTask $task, User $staff): ManualFulfillmentTask
    {
        return $this->withTask($task, function (ManualFulfillmentTask $locked) use ($staff): void {
            if ($locked->status !== TaskStatus::Open && ! ($locked->status === TaskStatus::Claimed && $locked->assigned_to === $staff->id)) {
                throw new ApplicationException('This task is not open.', 'conflict', 409);
            }

            $locked->forceFill(['status' => TaskStatus::Claimed, 'assigned_to' => $staff->id, 'claimed_at' => now()])->save();
            $this->audit->record(new AuditEntry('fulfillment.manual_task.claimed', $locked));
        });
    }

    public function complete(ManualFulfillmentTask $task, User $staff, string $note): ManualFulfillmentTask
    {
        return $this->finish($task, $staff, $note, TaskStatus::Done, Unit::Delivered, 'manual_delivered');
    }

    public function reject(ManualFulfillmentTask $task, User $staff, string $note): ManualFulfillmentTask
    {
        return $this->finish($task, $staff, $note, TaskStatus::Rejected, Unit::Failed, 'manual_rejected');
    }

    private function finish(ManualFulfillmentTask $task, User $staff, string $note, TaskStatus $to, Unit $unitTo, string $event): ManualFulfillmentTask
    {
        return $this->withTask($task, function (ManualFulfillmentTask $locked, Order $order, OrderItemUnit $unit) use ($staff, $note, $to, $unitTo, $event): void {
            if (! in_array($locked->status, [TaskStatus::Open, TaskStatus::Claimed], true) || ($locked->status === TaskStatus::Claimed && $locked->assigned_to !== $staff->id)) {
                throw new ApplicationException('Only an open task, or one you claimed, can be finished.', 'conflict', 409);
            }

            if ($unit->status !== Unit::ManualTask) {
                throw new ApplicationException('This unit is no longer waiting for manual fulfilment.', 'conflict', 409);
            }

            $locked->forceFill(['status' => $to, 'assigned_to' => $staff->id, 'completed_at' => now(), 'completion_note' => $note])->save();
            $actor = OrderActor::staff($staff);
            $this->units->move($unit, $unitTo, $event, null, ['manual_task_id' => $locked->id], $actor);
            $this->audit->record(new AuditEntry('fulfillment.manual_task.'.$to->value, $locked, after: ['unit_id' => $unit->id], reason: $note));
            $this->progress->advance($order, $actor);
        });
    }

    /**
     * @param  callable(ManualFulfillmentTask, Order, OrderItemUnit): void  $work
     */
    private function withTask(ManualFulfillmentTask $task, callable $work): ManualFulfillmentTask
    {
        DB::transaction(function () use ($task, $work): void {
            $order = Order::query()->lockForUpdate()->findOrFail(OrderItemUnit::query()->whereKey($task->order_item_unit_id)->value('order_id'));
            $unit = OrderItemUnit::query()->lockForUpdate()->findOrFail($task->order_item_unit_id);
            $locked = ManualFulfillmentTask::query()->lockForUpdate()->findOrFail($task->id);

            $work($locked, $order, $unit);
        }, 3);

        return $task->refresh()->load(['orderItemUnit.order:id,public_id,order_number,status', 'orderItemUnit.orderItem:id,name_snapshot,sku_snapshot,delivery_type']);
    }
}
