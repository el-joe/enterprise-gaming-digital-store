<?php

/**
 * Manual fulfilment of direct top-ups (owner decision BD2) and the admin review actions.
 */

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Fulfillment\Enums\ManualFulfillmentTaskStatus;
use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Fulfillment\Models\ManualFulfillmentTask;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemDeliveryValue;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Notifications\OrderDelivered;
use App\Domains\Orders\Notifications\OrderFulfilmentFailed;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Services\SettingsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    Notification::fake();
    Http::fake();
});

function topupOrder(int $quantity = 1): Order
{
    $order = paidOrderFor(sellableVariant('9'), $quantity, DeliveryType::DirectTopup);
    (new OrderItemDeliveryValue)->forceFill(['order_item_id' => $order->orderItems()->value('id'), 'field_code' => 'player_id', 'field_label' => 'Player ID', 'value' => 'PLAYER-778899'])->save();
    FulfilOrder::dispatch($order->id);

    return $order->fresh();
}

it('lists, shows, claims and completes a top-up task, completing the order', function (): void {
    $order = topupOrder();
    $task = ManualFulfillmentTask::query()->sole();
    $staff = actingAsStaffWith(['orders.fulfill_manual']);

    $this->getJson('/admin/api/manual-tasks?filter[status]=open')->assertOk()->assertJsonPath('meta.total', 1)->assertJsonMissingPath('data.0.delivery_values');
    $this->getJson("/admin/api/manual-tasks/{$task->id}")->assertOk()->assertJsonPath('data.delivery_values.0.value', 'PLAYER-778899');
    $this->postJson("/admin/api/manual-tasks/{$task->id}/claim")->assertOk()->assertJsonPath('data.status', 'claimed');
    $this->postJson("/admin/api/manual-tasks/{$task->id}/complete", ['reason' => 'Credited in the game console.'])->assertOk()->assertJsonPath('data.status', 'done');

    expect($order->fresh()->status)->toBe(OrderStatus::Completed)
        ->and(OrderItemUnit::query()->sole()->status)->toBe(Unit::Delivered)
        ->and($task->fresh()->assigned_to)->toBe($staff->id);
    Notification::assertSentOnDemand(OrderDelivered::class);
    Http::assertNothingSent();
});

it('refuses another staff member finishing a task claimed by someone else', function (): void {
    topupOrder();
    $task = ManualFulfillmentTask::query()->sole();
    actingAsStaffWith(['orders.fulfill_manual']);
    $this->postJson("/admin/api/manual-tasks/{$task->id}/claim")->assertOk();

    actingAsStaffWith(['orders.fulfill_manual']);
    $this->postJson("/admin/api/manual-tasks/{$task->id}/complete", ['reason' => 'Not mine to do'])->assertConflict();
    $this->postJson("/admin/api/manual-tasks/{$task->id}/claim")->assertConflict();
});

it('rejects a task and fails the unit towards refund', function (): void {
    $order = topupOrder();
    actingAsStaffWith(['orders.fulfill_manual', 'orders.review']);

    $this->postJson('/admin/api/manual-tasks/'.ManualFulfillmentTask::query()->value('id').'/reject', ['reason' => 'Player ID does not exist.'])->assertOk();

    expect(OrderItemUnit::query()->sole()->status)->toBe(Unit::Failed)
        ->and(ManualFulfillmentTask::query()->sole()->status)->toBe(ManualFulfillmentTaskStatus::Rejected)
        ->and($order->fresh()->status)->toBe(OrderStatus::RefundPending);
    Notification::assertSentOnDemand(OrderFulfilmentFailed::class);
});

it('keeps the order in review when the rejecting staff member cannot release it', function (): void {
    $order = topupOrder();
    actingAsStaffWith(['orders.fulfill_manual']);

    $this->postJson('/admin/api/manual-tasks/'.ManualFulfillmentTask::query()->value('id').'/reject', ['reason' => 'Player ID does not exist.'])->assertOk();

    expect($order->fresh()->status)->toBe(OrderStatus::ManualReview)
        ->and($order->orderEvents()->where('type', 'status_held')->exists())->toBeTrue();
});

it('requires the manual fulfilment permission', function (): void {
    topupOrder();
    actingAsStaffWith(['orders.view']);

    $this->getJson('/admin/api/manual-tasks')->assertForbidden();
});

it('lets staff approve a risk-held order, which then fulfils its units', function (): void {
    app(SettingsService::class)->set('security', 'risk_review_score', 5, SettingType::Int);
    $order = topupOrder();
    expect($order->status)->toBe(OrderStatus::ManualReview)->and(ManualFulfillmentTask::query()->exists())->toBeFalse();

    actingAsStaffWith(['orders.review']);
    $this->postJson("/admin/api/orders/{$order->public_id}/review", ['reason' => 'Customer verified by phone.'])->assertOk();

    expect(ManualFulfillmentTask::query()->count())->toBe(1)
        ->and(OrderItemUnit::query()->sole()->status)->toBe(Unit::ManualTask);
});

it('shows the order timeline to staff with orders.view', function (): void {
    $order = topupOrder();
    actingAsStaffWith(['orders.view']);

    $this->getJson("/admin/api/orders/{$order->public_id}/timeline")->assertOk()
        ->assertJsonFragment(['type' => 'unit.manual_task_created']);
});
