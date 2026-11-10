<?php

namespace App\Domains\Orders\DTOs;

use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\OrderEventActorType;

/**
 * Who asks for a transition. Staff actors must carry the user so their permission is checked.
 */
final readonly class OrderActor
{
    private function __construct(
        public OrderEventActorType $type,
        public ?User $user = null,
    ) {}

    public static function system(): self
    {
        return new self(OrderEventActorType::System);
    }

    public static function webhook(): self
    {
        return new self(OrderEventActorType::Webhook);
    }

    public static function customer(?User $user = null): self
    {
        return new self(OrderEventActorType::Customer, $user);
    }

    public static function staff(User $user): self
    {
        return new self(OrderEventActorType::Staff, $user);
    }

    public static function of(OrderEventActorType $type, ?User $user = null): self
    {
        return new self($type, $user);
    }

    public function id(): ?int
    {
        return $this->user?->getKey();
    }
}
