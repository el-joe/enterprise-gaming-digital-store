<?php

namespace App\View\Composers;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Repositories\InboxRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Data of the account layout (M13): the signed-in customer (name, email, verification) and the
 * unread notification count shown in the account navigation.
 */
class AccountLayoutComposer
{
    public function __construct(private Request $request, private InboxRepository $inbox) {}

    public function compose(View $view): void
    {
        $user = $this->request->user();

        if (! $user instanceof User) {
            return;
        }

        $view->with('account', [
            'name' => $user->name,
            'email' => $user->email,
            'email_verified' => $user->hasVerifiedEmail(),
            'unread' => $this->inbox->unreadCount($user),
        ]);
    }
}
