<?php

namespace App\Http\Requests\Mobile;

use App\Domains\Settings\Services\AppConfigService;
use App\Domains\Shared\ValueObjects\SemanticVersion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppConfigRequest extends FormRequest
{
    /**
     * Optional: the installed app's platform and version, to receive `update`.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'platform' => ['required_with:version', 'string', Rule::in(AppConfigService::PLATFORMS)],
            'version' => ['required_with:platform', 'string', 'max:64', 'regex:'.SemanticVersion::PATTERN],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['version.regex' => __('The version must be a semantic version such as 1.4.0.')];
    }
}
