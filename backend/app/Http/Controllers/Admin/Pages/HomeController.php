<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Http\Controllers\Controller;
use App\Http\Support\AdminNavigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * `/admin`: the first screen the staff member may open (the dashboard for most roles; the
 * payments refunds tab for a support agent, for example). 403 when none.
 */
class HomeController extends Controller
{
    public function __invoke(Request $request, AdminNavigation $navigation): RedirectResponse
    {
        $first = $navigation->items($request->user())[0] ?? null;

        abort_if($first === null, 403);

        return redirect()->to($first['url']);
    }
}
