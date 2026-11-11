<?php

namespace App\Domains\Notifications\Contracts;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\DTOs\PushMessage;

/**
 * A Laravel notification that can go to the `push` channel (M-A18, ADR-042). Returning null
 * sends nothing (for example when the record is gone).
 */
interface PushNotification
{
    public function toPush(User $notifiable): ?PushMessage;
}
