<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Promotions\Enums\PromotionType;
use App\Domains\Promotions\Models\Promotion;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Promotions and discount campaigns (spec Phase 9.8) with their targets.
 */
class PromotionScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'marketing';
    }

    public static function slug(): string
    {
        return 'promotions';
    }

    public static function viewPermission(): string
    {
        return 'marketing.promotions';
    }

    public static function managePermission(): string
    {
        return 'marketing.promotions';
    }

    public static function model(): string
    {
        return Promotion::class;
    }

    public static function table(): string
    {
        return 'promotions';
    }

    protected function apiPath(): string
    {
        return '/admin/api/promotions';
    }

    public function fields(?Model $record): array
    {
        return [
            'promotion' => [
                Field::text('name', $this->l('name'))->required()->max(120),
                Field::text('campaign', $this->l('campaign'))->nullable()->max(120),
                Field::select('type', $this->l('type'), Options::enum('promotion_type', PromotionType::cases()))->required(),
                Field::decimal('value', $this->l('value'))->required(),
                Field::text('badge_text', $this->l('badge_text'))->nullable()->max(40),
                Field::number('priority', $this->l('priority'))->default(0),
                Field::toggle('stackable', $this->l('stackable')),
                Field::toggle('is_active', $this->l('is_active'))->default(true),
                Field::datetime('starts_at', $this->l('starts_at'))->required()->nullable(false),
                Field::datetime('ends_at', $this->l('ends_at')),
            ],
            'targets' => [
                Field::json('targets', $this->l('targets'))->help(__('admin.help.targets_json'))
                    ->using(static fn (Model $promotion): array => $promotion->promotionTargets()->get(['target_type', 'target_id'])->map(static fn ($target): array => ['type' => $target->target_type?->value ?? $target->target_type, 'id' => $target->target_id])->all()),
            ],
        ];
    }
}
