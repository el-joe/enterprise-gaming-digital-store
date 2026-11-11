<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Domains\Analytics\Services\AnalyticsSettings;
use App\Domains\Seo\SearchConsole\SearchConsoleConnection;
use App\Domains\Seo\SearchConsole\SearchConsoleException;
use App\Domains\Seo\SearchConsole\SearchConsoleReport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Seo\SearchConsoleCallbackRequest;
use App\Http\Requests\Admin\Seo\SearchConsoleReportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Search Console (M18, INTEGRATIONS.md section 7): the report page (`seo.manage`) and the
 * one-time OAuth consent (`settings.manage_critical`): connect sends the admin to Google, the
 * callback stores the refresh token write-only and returns to Settings > Analytics.
 */
class SearchConsolePageController extends Controller
{
    private const array WINDOWS = [7, 28, 90];

    public function report(SearchConsoleReportRequest $request, SearchConsoleReport $report, AnalyticsSettings $settings): Response
    {
        $days = (int) ($request->validated('days') ?? 28);

        return response()->view('admin.content.search-console', [
            'days' => $days,
            'windows' => self::WINDOWS,
            'report' => $report->summary($days),
            'connected' => $settings->searchConsoleConnected(),
            'property' => $settings->searchConsoleProperty(),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function connect(Request $request, SearchConsoleConnection $connection): RedirectResponse
    {
        try {
            return redirect()->away($connection->start($request->session(), route('admin.content.search-console.callback')));
        } catch (SearchConsoleException $exception) {
            return redirect()->route('admin.settings.show', 'analytics')->with('search_console_error', $exception->getMessage());
        }
    }

    public function callback(SearchConsoleCallbackRequest $request, SearchConsoleConnection $connection): RedirectResponse
    {
        try {
            $connection->complete(
                $request->session(),
                $request->user(),
                $request->validated('state'),
                $request->validated('code'),
                $request->validated('error'),
                route('admin.content.search-console.callback'),
            );
        } catch (SearchConsoleException $exception) {
            return redirect()->route('admin.settings.show', 'analytics')->with('search_console_error', $exception->getMessage());
        }

        return redirect()->route('admin.settings.show', 'analytics')->with('search_console_status', __('admin.search_console.connected_now'));
    }
}
