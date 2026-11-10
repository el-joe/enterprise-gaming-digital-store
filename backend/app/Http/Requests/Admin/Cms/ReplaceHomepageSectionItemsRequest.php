<?php

namespace App\Http\Requests\Admin\Cms;

use App\Domains\Shared\Rules\MorphTargetExists;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReplaceHomepageSectionItemsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['present', 'array', 'max:50'],
            'items.*.item_type' => ['required', Rule::in(['product', 'gaming_platform', 'category', 'banner', 'faq'])],
            'items.*.item_id' => ['required', 'integer', 'min:1', new MorphTargetExists('item_type')],
        ];
    }
}
