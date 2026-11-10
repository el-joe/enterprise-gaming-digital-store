<?php

namespace App\Http\Requests\Admin\Cms;

use App\Domains\Cms\Enums\HomepageSectionSource;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveHomepageSectionRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => [$this->presence(), 'string', 'max:64', 'alpha_dash:ascii', Rule::unique('homepage_sections', 'key')->ignore($this->route('homepageSection'))],
            'title' => ['sometimes', 'nullable', 'string', 'max:190'],
            'subtitle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'source' => [$this->presence(), Rule::enum(HomepageSectionSource::class)],
            'settings' => ['sometimes', 'nullable', 'array'],
            'is_active' => [$this->presence(), 'boolean'],
            'sort_order' => [$this->presence(), 'integer'],
        ];
    }
}
