<?php

namespace App\Domains\Promotions\Services;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Pricing\Services\PricingDirtyMarker;
use App\Domains\Promotions\Models\Promotion;
use App\Domains\Promotions\Models\PromotionTarget;
use App\Domains\Shared\Services\CrudService;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Database\Eloquent\Model;

/**
 * Admin management of promotions and their targets. The pricing engine (M6) applies them; any
 * change marks every variant for `pricing:recalculate-dirty`, since a promotion can reach any
 * variant through its platform or category.
 *
 * @extends CrudService<Promotion>
 */
class PromotionService extends CrudService
{
    public function __construct(ListQueryApplier $lists, AuditLogger $audit, private PricingDirtyMarker $dirty)
    {
        parent::__construct($lists, $audit);
    }

    protected function model(): string
    {
        return Promotion::class;
    }

    protected function auditPrefix(): string
    {
        return 'promotions.promotion';
    }

    protected function filters(): array
    {
        return ['is_active' => 'bool', 'type' => 'exact', 'campaign' => 'exact'];
    }

    protected function sorts(): array
    {
        return ['id', 'priority', 'starts_at', 'ends_at'];
    }

    protected function searchColumns(): array
    {
        return ['name', 'campaign'];
    }

    protected function relations(): array
    {
        return ['promotionTargets'];
    }

    /**
     * @param  Promotion  $model
     */
    protected function afterSave(Model $model, array $attributes): void
    {
        if (array_key_exists('targets', $attributes)) {
            PromotionTarget::query()->where('promotion_id', $model->id)->delete();

            foreach (collect($attributes['targets'])->unique(static fn (array $target): string => $target['type'].':'.$target['id']) as $target) {
                (new PromotionTarget)->forceFill(['promotion_id' => $model->id, 'target_type' => $target['type'], 'target_id' => (int) $target['id']])->save();
            }
        }

        $this->dirty->all();
    }

    protected function afterDelete(Model $model): void
    {
        $this->dirty->all();
    }
}
