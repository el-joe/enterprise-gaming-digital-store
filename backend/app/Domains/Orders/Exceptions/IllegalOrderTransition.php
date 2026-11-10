<?php

namespace App\Domains\Orders\Exceptions;

use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Shared\Exceptions\ApplicationException;

class IllegalOrderTransition extends ApplicationException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 'illegal_transition', 409);
    }

    public static function between(OrderStatus $from, OrderStatus $to): self
    {
        return new self("An order cannot move from {$from->value} to {$to->value}.");
    }

    public static function actorNotAllowed(OrderStatus $from, OrderStatus $to, string $actor): self
    {
        return new self("The {$actor} actor cannot move an order from {$from->value} to {$to->value}.");
    }

    public static function withoutVerifiedPayment(OrderStatus $to): self
    {
        return new self("An order without a verified payment cannot move to {$to->value}.");
    }

    public static function directWrite(): self
    {
        return new self('Order status can only be changed through the order state machine.');
    }
}
