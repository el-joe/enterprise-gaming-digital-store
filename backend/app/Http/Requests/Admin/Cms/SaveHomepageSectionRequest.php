<?php

namespace App\Http\Requests\Admin\Cms;

use App\Domains\Cms\Enums\HomepageAutoRule;
use App\Domains\Cms\Enums\HomepageSectionSource;
use App\Domains\Cms\Enums\HomepageSectionType;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * A homepage section. Storefront v2 (Figma 116:11898): the drawer sends the type, rule, period,
 * category or brand and item limit in `settings`, and the Arabic title as `title_ar`; the key
 * and position are generated on create when left out. "See all" is never sent: it is computed.
 */
class SaveHomepageSectionRequest extends AdminSaveRequest
{
    public const array PERIODS = ['all', '7d', '30d', '90d'];

    /** Rules the drawer offers for a product rail (`manual` is a manual pick of products). */
    public const array RAIL_RULES = ['best_sellers', 'newest', 'on_sale', 'category', 'brand', 'manual'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = array_values(array_unique([...self::RAIL_RULES, ...array_map(static fn (HomepageAutoRule $rule): string => $rule->value, HomepageAutoRule::cases())]));

        return [
            'key' => ['sometimes', 'string', 'max:64', 'alpha_dash:ascii', Rule::unique('homepage_sections', 'key')->ignore($this->route('homepageSection'))],
            'title' => ['sometimes', 'nullable', 'string', 'max:190'],
            'title_ar' => ['sometimes', 'nullable', 'string', 'max:190'],
            'subtitle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'source' => [$this->presence(), Rule::enum(HomepageSectionSource::class)],
            'settings' => ['sometimes', 'nullable', 'array'],
            'settings.type' => ['sometimes', 'nullable', Rule::enum(HomepageSectionType::class)],
            'settings.rule' => ['sometimes', 'nullable', Rule::in($rules)],
            'settings.period' => ['sometimes', 'nullable', Rule::in(self::PERIODS)],
            'settings.limit' => ['sometimes', 'nullable', 'integer', 'min:4', 'max:24'],
            'settings.category' => ['nullable', 'required_if:settings.rule,category', 'string', Rule::exists('categories', 'slug')->whereNull('deleted_at')],
            'settings.platform' => ['nullable', 'required_if:settings.rule,brand', 'string', Rule::exists('gaming_platforms', 'slug')->whereNull('deleted_at')],
            'is_active' => [$this->presence(), 'boolean'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
