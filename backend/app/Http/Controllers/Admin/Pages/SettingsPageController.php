<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Domains\Settings\Services\AdminSettingsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

/**
 * Settings (M15, spec Phase 9.12, Figma "Admin Settings" 21:6986): a group list on the side and
 * one group's form. Secrets are write-only (configured or not, with a replace form). Groups the
 * viewer may not save are read-only.
 */
class SettingsPageController extends Controller
{
    public function __invoke(AdminSettingsService $settings, ?string $group = null): Response
    {
        $groups = $settings->groups();
        $group ??= $groups[0];

        return response()->view('admin.settings.show', [
            'groups' => $groups,
            'screen' => $settings->screen($group),
        ])->header('Cache-Control', 'no-store, private');
    }
}
