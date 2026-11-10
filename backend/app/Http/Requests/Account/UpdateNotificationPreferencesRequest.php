<?php

namespace App\Http\Requests\Account;

use App\Domains\Notifications\Enums\NotificationPreferenceCategory;
use App\Domains\Notifications\Enums\NotificationPreferenceChannel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PATCH /account/notification-preferences (M-A5): `preferences`, a list of
 * `{ category, channel, enabled }`; cells not sent are unchanged.
 */
class UpdateNotificationPreferencesRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'preferences' => ['required', 'array', 'min:1', 'max:'.(count(NotificationPreferenceCategory::cases()) * count(NotificationPreferenceChannel::cases()))],
            'preferences.*' => ['required', 'array:category,channel,enabled'],
            'preferences.*.category' => ['required', 'string', Rule::enum(NotificationPreferenceCategory::class)],
            'preferences.*.channel' => ['required', 'string', Rule::enum(NotificationPreferenceChannel::class)],
            'preferences.*.enabled' => ['required', 'boolean'],
        ];
    }

    /**
     * @return list<array{category: NotificationPreferenceCategory, channel: NotificationPreferenceChannel, enabled: bool}>
     */
    public function changes(): array
    {
        return array_values(array_map(static fn (array $row): array => [
            'category' => NotificationPreferenceCategory::from($row['category']),
            'channel' => NotificationPreferenceChannel::from($row['channel']),
            'enabled' => filter_var($row['enabled'], FILTER_VALIDATE_BOOLEAN),
        ], $this->validated('preferences')));
    }
}
