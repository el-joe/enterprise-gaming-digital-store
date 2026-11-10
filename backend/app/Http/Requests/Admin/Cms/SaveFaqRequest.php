<?php

namespace App\Http\Requests\Admin\Cms;

use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveFaqRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'faq_group_id' => [$this->presence(), 'integer', Rule::exists('faq_groups', 'id')],
            'question' => [$this->presence(), 'string', 'max:500'],
            'answer' => [$this->presence(), 'string'],
            'show_on_homepage' => ['sometimes', 'boolean'],
            'is_active' => [$this->presence(), 'boolean'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
