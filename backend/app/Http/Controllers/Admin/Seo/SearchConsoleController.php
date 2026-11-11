<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Domains\Seo\SearchConsole\SearchConsoleConnection;
use App\Domains\Seo\SearchConsole\SearchConsoleException;
use App\Domains\Seo\SearchConsole\SearchConsoleSync;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Search Console JSON actions (M18): disconnect (revokes and deletes the stored token,
 * `settings.manage_critical`) and fetch now (`seo.manage`). Responses never contain a token.
 */
class SearchConsoleController extends Controller
{
    public function disconnect(Request $request, SearchConsoleConnection $connection): JsonResponse
    {
        $connection->disconnect($request->user());

        return $this->success(['connected' => false], __('admin.search_console.disconnected'));
    }

    public function sync(SearchConsoleSync $sync): JsonResponse
    {
        try {
            $rows = $sync->sync();
        } catch (SearchConsoleException $exception) {
            throw new ApplicationException($exception->getMessage(), 'search_console_unavailable', 502);
        }

        return $this->success(['rows' => $rows], __('admin.search_console.synced', ['rows' => $rows]));
    }
}
