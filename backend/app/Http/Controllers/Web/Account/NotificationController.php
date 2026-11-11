<?php

namespace App\Http\Controllers\Web\Account;

use App\Domains\Notifications\Repositories\InboxRepository;
use App\Domains\Notifications\Services\NotificationPreferenceService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Account\AccountPageRequest;
use App\Http\Resources\Account\InboxNotificationResource;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Notification inbox and preferences (M13). Marking read and saving preferences go through the
 * JSON routes (`/api/v1/account/notifications/*`, `/notification-preferences`).
 */
class NotificationController extends Controller
{
    public function index(AccountPageRequest $request, InboxRepository $inbox): View
    {
        $page = $inbox->paginate($request->user(), false, $request->page(), AccountPageRequest::PER_PAGE)->withPath($request->url());

        return view('account.notifications.index', [
            'notifications' => InboxNotificationResource::collection($page->getCollection())->resolve($request),
            'paginator' => $page,
            'unread' => $inbox->unreadCount($request->user()),
        ]);
    }

    public function preferences(Request $request, NotificationPreferenceService $preferences): View
    {
        return view('account.notifications.preferences', [
            'preferences' => collect($preferences->settings($request->user()))->map->toArray()->groupBy('category')->all(),
        ]);
    }
}
