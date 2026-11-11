<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The fields of one settings group (M15). Each field's rules come from
 * `config('admin_settings')` and are applied by AdminSettingsService::save, so the screen, the
 * API and the stored types share one definition; unknown keys are ignored there.
 */
class SaveSettingsRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function values(): array
    {
        return $this->json()->all();
    }
}
