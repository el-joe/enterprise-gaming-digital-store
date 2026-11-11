<?php

namespace App\DataTables\Pricing;

use App\DataTables\AdminDataTable;
use App\DataTables\Cell;
use App\DataTables\Column;
use App\DataTables\Filter;
use App\Domains\Pricing\Enums\PricingRuleKind;
use App\Domains\Pricing\Enums\PricingRuleScope;
use App\Domains\Pricing\Models\PricingRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Pricing and markup rules (spec Phase 9.7).
 */
class PricingRulesDataTable extends AdminDataTable
{
    public static function key(): string
    {
        return 'pricing-rules';
    }

    public static function permission(): string
    {
        return 'pricing.view';
    }

    public function columns(): array
    {
        return [
            new Column('name', __('admin.col.name'), 'pricing_rules.name', html: true),
            new Column('kind', __('admin.col.kind'), 'pricing_rules.kind'),
            new Column('scope', __('admin.col.scope'), 'pricing_rules.scope'),
            new Column('market', __('admin.col.market'), html: true),
            new Column('markup', __('admin.col.markup'), html: true),
            new Column('min_margin', __('admin.col.min_margin'), html: true),
            new Column('priority', __('admin.col.priority'), 'pricing_rules.priority', numeric: true),
            new Column('active', __('admin.col.active'), 'pricing_rules.is_active', html: true),
        ];
    }

    public function defaultOrder(): array
    {
        return ['priority', 'desc'];
    }

    protected function searchColumns(): array
    {
        return ['pricing_rules.name'];
    }

    public function filters(): array
    {
        return [
            new Filter('scope', __('admin.col.scope'), 'select', static function (Builder $query, string $value): void {
                $query->where('pricing_rules.scope', $value);
            }, $this->enumOptions('pricing_scope', PricingRuleScope::cases())),
            new Filter('kind', __('admin.col.kind'), 'select', static function (Builder $query, string $value): void {
                $query->where('pricing_rules.kind', $value);
            }, $this->enumOptions('pricing_kind', PricingRuleKind::cases())),
            $this->booleanFilter('active', __('admin.col.active'), 'pricing_rules.is_active'),
        ];
    }

    protected function query(): Builder
    {
        return PricingRule::query()->select('pricing_rules.*');
    }

    /**
     * @param  PricingRule  $row
     */
    protected function cells(Model $row): array
    {
        $edit = route('admin.pricing.pricing-rules.edit', ['record' => $row->getRouteKey()]);

        return [
            'name' => Cell::link($edit, (string) $row->name),
            'kind' => Cell::label('pricing_kind', $row->kind?->value),
            'scope' => Cell::label('pricing_scope', $row->scope?->value).($row->scope_id ? ' #'.$row->scope_id : ''),
            'market' => Cell::mono(trim(($row->country_code ?? '*').' · '.($row->currency ?? '*'))),
            'markup' => Cell::mono(trim(($row->percent_markup !== null ? $row->percent_markup.'%' : '').' '.($row->fixed_markup !== null ? '+'.$row->fixed_markup : '')) ?: null),
            'min_margin' => Cell::mono($row->min_margin_pct !== null ? $row->min_margin_pct.'%' : ($row->min_margin_fixed !== null ? (string) $row->min_margin_fixed : null)),
            'priority' => (string) $row->priority,
            'active' => Cell::yesNo((bool) $row->is_active),
        ];
    }
}
