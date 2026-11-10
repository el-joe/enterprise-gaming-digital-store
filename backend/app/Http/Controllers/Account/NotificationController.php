<?php

namespace App\Http\Controllers\Account;

use App\Domains\Notifications\Repositories\InboxRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ListNotificationsRequest;
use App\Http\Resources\Account\InboxNotificationResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

/**
 * The customer's notification inbox (M-A5, gap G6, inbox only; push is M-A18).
 */
class NotificationController extends Controller
{
    public function __construct(private InboxRepository $inbox) {}

    public function index(ListNotificationsRequest $request): JsonResponse
    {
        return $this->paginated(
            $this->inbox->paginate($request->user(), $request->unreadOnly(), $request->page(), $request->perPage()),
            InboxNotificationResource::class,
            ['unread_count' => $this->inbox->unreadCount($request->user())],
        );
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return $this->success(['unread_count' => $this->inbox->unreadCount($request->user())]);
    }

    public function read(Request $request, string $notification): JsonResponse
    {
        $read = $this->inbox->markRead($request->user(), $notification) ?? throw (new ModelNotFoundException)->setModel(DatabaseNotification::class);

        return $this->success(['notification' => InboxNotificationResource::make($read)->resolve()]);
    }

    public function readAll(Request $request): JsonResponse
    {
        return $this->success(['marked_read' => $this->inbox->markAllRead($request->user()), 'unread_count' => 0]);
    }
}
