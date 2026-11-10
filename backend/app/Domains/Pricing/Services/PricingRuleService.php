<?php

namespace App\Domains\Pricing\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\Models\User;
use App\Domains\Pricing\Enums\PricingRuleBelowMarginPolicy;
use App\Domains\Pricing\Models\PricingRule;
use App\Domains\Pricing\Repositories\PricingRuleRepository;
use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Services\CrudService;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * @extends CrudService<PricingRule>
 */
class PricingRuleService extends CrudService
{
    public function __construct(
        ListQueryApplier $lists,
        AuditLogger $audit,
        private PricingRuleRepository $rules,
        private PricingDirtyMarker $dirty,
        private MarginAuthorizer $margin,
    ) {
        parent::__construct($lists, $audit);
    }

    public function list(ListQuery $list): LengthAwarePaginator
    {
        return $this->rules->adminPage($list);
    }

    protected function model(): string
    {
        return PricingRule::class;
    }

    protected function auditPrefix(): string
    {
        return 'pricing.rule';
    }

    /**
     * @param  PricingRule  $model
     */
    protected function prepare(Model $model, array $attributes): array
    {
        $policy = $attributes['below_margin_policy'] ?? null;

        if ($policy === PricingRuleBelowMarginPolicy::Allow->value && $model->below_margin_policy !== PricingRuleBelowMarginPolicy::Allow) {
            $user = Auth::user();

            $this->margin->assertAuthorized(
                $user instanceof User ? $user : null,
                $attributes['override_reason'] ?? null,
                'override_reason',
                'Setting the below-margin policy to allow needs the margin override permission and a reason.',
            );

            $this->audit->record(new AuditEntry(
                action: 'pricing.margin_policy_allowed',
                subject: $model->exists ? $model : null,
                after: ['below_margin_policy' => $policy, 'rule_name' => $attributes['name'] ?? $model->name],
                reason: $attributes['override_reason'],
            ));
        }

        if ($model->exists) {
            $this->dirty->forRule($model);
        }

        return $attributes;
    }

    /**
     * @param  PricingRule  $model
     */
    protected function afterSave(Model $model, array $attributes): void
    {
        $this->dirty->forRule($model);
    }

    /**
     * @param  PricingRule  $model
     */
    protected function afterDelete(Model $model): void
    {
        $this->dirty->forRule($model);
    }
}
