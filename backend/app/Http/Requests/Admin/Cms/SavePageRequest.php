<?php

namespace App\Http\Requests\Admin\Cms;

use App\Domains\Cms\Enums\PageStatus;
use App\Domains\Shared\Rules\Slug;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SavePageRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [$this->presence(), 'string', 'max:190'],
            'slug' => [$this->presence(), 'string', 'max:210', new Slug, Rule::unique('pages', 'slug')->ignore($this->route('page'))],
            'body' => [$this->presence(), 'string'],
            'template' => ['sometimes', 'nullable', 'string', 'max:64'],
            'status' => ['sometimes', Rule::enum(PageStatus::class)],
        ];
    }
}
