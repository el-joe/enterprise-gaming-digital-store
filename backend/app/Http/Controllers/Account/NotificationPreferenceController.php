<?php

namespace App\Http\Controllers\Account;

use App\Domains\Notifications\DTOs\PreferenceSetting;
use App\Domains\Notifications\Services\NotificationPreferenceService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateNotificationPreferencesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET and PATCH /account/notification-preferences (M-A5): category x channel
 * (`mail`, `database`, `sms`), with the mandatory cells marked.
 */
class NotificationPreferenceController extends Controller
{
    public function __construct(private NotificationPreferenceService $preferences) {}

    public function show(Request $request): JsonResponse
    {
        return $this->success(['preferences' => self::present($this->preferences->settings($request->user()))]);
    }

    public function update(UpdateNotificationPreferencesRequest $request): JsonResponse
    {
        return $this->success(['preferences' => self::present($this->preferences->update($request->user(), $request->changes()))], 'Preferences saved.');
    }

    /**
     * @param  list<PreferenceSetting>  $settings
     * @return list<array{category: string, channel: string, enabled: bool, mandatory: bool}>
     */
    private static function present(array $settings): array
    {
        return array_map(static fn (PreferenceSetting $setting): array => $setting->toArray(), $settings);
    }
}
