<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Domains\Analytics\Services\AdminDashboardService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Pages\DashboardRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardRequest $request, AdminDashboardService $dashboard): View
    {
        return view('admin.dashboard', [
            'dashboard' => $dashboard->build($request->user(), $request->days()),
            'ranges' => (array) config('admin.dashboard.ranges'),
        ]);
    }
}
