<?php

namespace App\Http\Requests\Admin\Cms;

use App\Domains\Shared\Rules\MorphTargetExists;
use App\Domains\Shared\Rules\SafeUrl;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveMenuItemRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parent_id' => ['sometimes', 'nullable', 'integer', Rule::exists('menu_items', 'id')],
            'label' => [$this->presence(), 'string', 'max:120'],
            'url' => ['sometimes', 'nullable', 'string', 'max:500', new SafeUrl],
            'linkable_type' => ['sometimes', 'nullable', Rule::in(['category', 'gaming_platform', 'product', 'page', 'blog_post'])],
            'linkable_id' => ['required_with:linkable_type', 'nullable', 'integer', 'min:1', new MorphTargetExists('linkable_type')],
            'target' => ['sometimes', 'nullable', Rule::in(['_self', '_blank'])],
            'sort_order' => ['sometimes', 'integer'],
            'is_active' => [$this->presence(), 'boolean'],
        ];
    }
}
