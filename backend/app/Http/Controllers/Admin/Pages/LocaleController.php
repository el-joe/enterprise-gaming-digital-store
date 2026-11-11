<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Admin\SetAdminLocale;
use App\Http\Requests\Admin\Pages\SwitchLocaleRequest;
use Illuminate\Http\RedirectResponse;

/**
 * The admin language switch (a form post, CSRF). Returns to the admin page it came from (path
 * only, same host); anywhere else goes to the admin sign-in, which forwards signed-in staff.
 */
class LocaleController extends Controller
{
    public function __invoke(SwitchLocaleRequest $request): RedirectResponse
    {
        $request->session()->put(SetAdminLocale::SESSION_KEY, $request->validated('locale'));

        $back = parse_url((string) $request->headers->get('referer', ''));
        $sameHost = ! isset($back['host']) || $back['host'] === $request->getHost();
        $path = (string) ($back['path'] ?? '');

        return redirect()->to($sameHost && preg_match('#^/admin(/|$)#', $path) === 1 ? url($path) : route('admin.login'));
    }
}
