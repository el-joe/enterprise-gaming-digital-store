<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveProductImageRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'variant_id' => ['sometimes', 'nullable', 'integer', Rule::exists('product_variants', 'id')->where('product_id', $this->route('product')->id)],
            'media_id' => [$this->presence(), 'integer', Rule::exists('media', 'id')->whereNull('deleted_at')],
            'alt' => ['sometimes', 'nullable', 'string', 'max:190'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
