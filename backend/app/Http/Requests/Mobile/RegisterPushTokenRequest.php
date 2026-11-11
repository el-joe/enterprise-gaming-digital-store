<?php

namespace App\Http\Requests\Mobile;

use App\Domains\Notifications\Enums\PushPlatform;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * POST /api/v1/mobile/push-token (M-A18): the device's FCM registration token and its platform.
 */
class RegisterPushTokenRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'min:20', 'max:4096', 'regex:/^[A-Za-z0-9_:\-.]+$/'],
            'platform' => ['required', 'string', Rule::enum(PushPlatform::class)],
        ];
    }

    public function platform(): PushPlatform
    {
        return PushPlatform::from($this->string('platform')->toString());
    }
}
