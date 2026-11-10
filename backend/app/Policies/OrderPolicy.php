<?php

namespace App\Policies;

use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\Order;
use Illuminate\Auth\Access\Response;

/**
 * Object-level authorization of a customer's order (M-A5). Another customer's order, and any
 * guest order, is denied as not found so the response does not reveal that it exists.
 */
class OrderPolicy
{
    public function view(User $user, Order $order): Response
    {
        return $order->user_id !== null && $order->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
