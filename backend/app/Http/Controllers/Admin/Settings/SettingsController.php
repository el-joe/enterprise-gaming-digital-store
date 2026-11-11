<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Domains\Settings\Services\AdminSettingsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\SaveSettingsRequest;
use App\Http\Requests\Admin\Settings\SetSecretRequest;
use Illuminate\Http\JsonResponse;

/**
 * Settings groups and write-only secrets (M15, spec Phase 9.12). The group's own permission
 * (`settings.manage`, or `settings.manage_critical` for critical groups and every secret) is
 * checked by AdminSettingsService; responses never contain a secret.
 */
class SettingsController extends Controller
{
    public function __construct(private AdminSettingsService $settings) {}

    public function update(SaveSettingsRequest $request, string $group): JsonResponse
    {
        $this->settings->save($request->user(), $group, $request->values());

        return $this->success(message: 'Settings saved.');
    }

    public function secret(SetSecretRequest $request, string $group, string $key): JsonResponse
    {
        $value = $request->validated('value');
        $this->settings->setSecret($request->user(), $group, $key, $value === null || $value === '' ? null : (string) $value);

        return $this->success(['configured' => $value !== null && $value !== ''], $value === null || $value === '' ? 'Secret cleared.' : 'Secret replaced.');
    }
}
