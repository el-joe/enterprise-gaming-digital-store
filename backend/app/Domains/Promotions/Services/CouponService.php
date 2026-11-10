<?php

namespace App\Domains\Promotions\Services;

use App\Domains\Promotions\Enums\CouponCreatedByType;
use App\Domains\Promotions\Models\Coupon;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Shared\Services\CrudService;
use Illuminate\Database\Eloquent\Model;

/**
 * Admin management of coupons (audited, SECURITY.md 9). Codes are stored upper case. `used_count`
 * is written only by the coupon contract under the coupon row lock; a global limit cannot be set
 * below the uses already held. The creator is staff unless the caller passes a
 * `CouponCreatedByType` instance as `created_by_type` (the MCP coupon action does); validated
 * admin input never carries one.
 *
 * @extends CrudService<Coupon>
 */
class CouponService extends CrudService
{
    protected function model(): string
    {
        return Coupon::class;
    }

    protected function auditPrefix(): string
    {
        return 'promotions.coupon';
    }

    protected function filters(): array
    {
        return ['is_active' => 'bool', 'type' => 'exact'];
    }

    protected function sorts(): array
    {
        return ['id', 'created_at', 'code', 'ends_at', 'used_count'];
    }

    protected function searchColumns(): array
    {
        return ['code'];
    }

    /**
     * @param  Coupon  $model
     */
    protected function prepare(Model $model, array $attributes): array
    {
        if (isset($attributes['code'])) {
            $attributes['code'] = DatabaseCheckoutCoupons::normalize((string) $attributes['code']);
        }

        if (isset($attributes['currency'])) {
            $attributes['currency'] = strtoupper((string) $attributes['currency']);
        }

        if (array_key_exists('usage_limit', $attributes) && $attributes['usage_limit'] !== null && $model->exists && (int) $attributes['usage_limit'] < $model->used_count) {
            throw new ApplicationException('The usage limit cannot be lower than the uses already held.', 'validation_failed', 422, ['usage_limit' => ['The usage limit cannot be lower than the uses already held.']]);
        }

        return $attributes;
    }

    /**
     * @param  Coupon  $model
     */
    protected function applyGuarded(Model $model, array $data): void
    {
        if (! $model->exists) {
            $creator = $data['created_by_type'] ?? null;

            $model->forceFill(['created_by_type' => $creator instanceof CouponCreatedByType ? $creator : CouponCreatedByType::Staff]);
        }
    }
}
