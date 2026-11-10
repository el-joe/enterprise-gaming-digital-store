<?php

/**
 * Admin management of promotions (applied by the M6 pricing engine).
 */

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Promotions\Models\Promotion;
use App\Domains\Promotions\Models\PromotionTarget;

it('creates a promotion with targets, replaces them on update and marks prices for recalculation', function (): void {
    actingAsStaffWith(['marketing.promotions']);
    $variant = sellableVariant('10');
    $category = Category::factory()->create();
    ProductVariant::query()->update(['pricing_dirty_at' => null]);

    $id = $this->postJson('/admin/api/promotions', [
        'name' => 'Spring sale', 'type' => 'percent', 'value' => '10', 'starts_at' => now()->toIso8601String(), 'is_active' => true,
        'targets' => [['type' => 'variant', 'id' => $variant->id], ['type' => 'category', 'id' => $category->id], ['type' => 'variant', 'id' => $variant->id]],
    ])->assertCreated()->assertJsonCount(2, 'data.targets')->json('data.id');

    expect($variant->fresh()->pricing_dirty_at)->not->toBeNull();

    $this->patchJson("/admin/api/promotions/{$id}", ['targets' => [['type' => 'product', 'id' => $variant->product_id]]])
        ->assertOk()->assertJsonPath('data.targets', [['type' => 'product', 'id' => $variant->product_id]]);
    $this->getJson("/admin/api/promotions/{$id}")->assertOk()->assertJsonPath('data.name', 'Spring sale');

    $this->deleteJson("/admin/api/promotions/{$id}")->assertOk();
    expect(Promotion::withTrashed()->find($id)->trashed())->toBeTrue()
        ->and(PromotionTarget::query()->where('promotion_id', $id)->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'promotions.promotion.created')->exists())->toBeTrue();
});

it('validates promotion input', function (array $body, string $field): void {
    actingAsStaffWith(['marketing.promotions']);

    $this->postJson('/admin/api/promotions', ['name' => 'X', 'type' => 'fixed', 'value' => '1', 'starts_at' => now()->toIso8601String(), 'is_active' => true, ...$body])
        ->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    'percent above 100' => [['type' => 'percent', 'value' => '150'], 'value'],
    'unknown target' => [['targets' => [['type' => 'product', 'id' => 999999]]], 'targets.0.id'],
    'bad target type' => [['targets' => [['type' => 'brand', 'id' => 1]]], 'targets.0.type'],
    'ends before it starts' => [['ends_at' => now()->subDay()->toIso8601String()], 'ends_at'],
]);

it('needs marketing.promotions', function (): void {
    actingAsStaffWith(['marketing.coupons']);

    $this->getJson('/admin/api/promotions')->assertForbidden();
});
