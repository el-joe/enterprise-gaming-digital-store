<?php

namespace App\Http\Requests\Admin\Cms;

use App\Domains\Cms\Enums\BannerPlacement;
use App\Domains\Shared\Rules\SafeUrl;
use App\Http\Requests\Admin\AdminSaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class SaveBannerRequest extends AdminSaveRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'placement' => [$this->presence(), Rule::enum(BannerPlacement::class)],
            'title' => ['sometimes', 'nullable', 'string', 'max:190'],
            'subtitle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cta_label' => ['sometimes', 'nullable', 'string', 'max:64'],
            'cta_url' => ['sometimes', 'nullable', 'string', 'max:500', new SafeUrl],
            'media_id' => [$this->presence(), 'integer', Rule::exists('media', 'id')->whereNull('deleted_at')],
            'mobile_media_id' => ['sometimes', 'nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at')],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after:starts_at'],
            'is_active' => [$this->presence(), 'boolean'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
