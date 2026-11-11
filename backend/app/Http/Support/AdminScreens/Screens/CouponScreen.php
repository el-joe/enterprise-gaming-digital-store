<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Promotions\Enums\CouponType;
use App\Domains\Promotions\Models\Coupon;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Coupons (spec Phase 9.8): limits, restrictions, schedule; the margin guard applies at checkout.
 */
class CouponScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'marketing';
    }

    public static function slug(): string
    {
        return 'coupons';
    }

    public static function viewPermission(): string
    {
        return 'marketing.coupons';
    }

    public static function managePermission(): string
    {
        return 'marketing.coupons';
    }

    public static function model(): string
    {
        return Coupon::class;
    }

    public static function table(): string
    {
        return 'coupons';
    }

    protected function apiPath(): string
    {
        return '/admin/api/coupons';
    }

    public function fields(?Model $record): array
    {
        return [
            'coupon' => [
                Field::text('code', $this->l('code'))->required()->max(64)->ltr(),
                Field::select('type', $this->l('type'), Options::enum('coupon_type', CouponType::cases()))->required(),
                Field::decimal('value', $this->l('value'))->required()->help(__('admin.help.coupon_value')),
                Field::select('currency', $this->l('currency'), Options::currencies())->nullable()->help(__('admin.help.coupon_currency')),
                Field::decimal('max_discount_base', $this->l('max_discount_base'))->nullable()->help(__('admin.help.base_money')),
                Field::decimal('min_order_base', $this->l('min_order_base'))->nullable()->help(__('admin.help.base_money')),
                Field::toggle('is_active', $this->l('is_active'))->default(true),
            ],
            'limits' => [
                Field::number('usage_limit', $this->l('usage_limit'))->nullable(),
                Field::number('usage_limit_per_user', $this->l('usage_limit_per_user'))->nullable(),
                Field::toggle('first_order_only', $this->l('first_order_only')),
                Field::toggle('stackable', $this->l('stackable')),
                Field::toggle('allow_below_margin', $this->l('allow_below_margin')),
                Field::datetime('starts_at', $this->l('starts_at')),
                Field::datetime('ends_at', $this->l('ends_at')),
            ],
            'restrictions' => [
                Field::multi('applicable_to.platforms', $this->l('platforms'), Options::platforms()),
                Field::multi('applicable_to.categories', $this->l('categories'), Options::categories()),
                Field::csv('applicable_to.products', $this->l('product_ids'))->help(__('admin.help.ids_csv')),
            ],
        ];
    }
}
