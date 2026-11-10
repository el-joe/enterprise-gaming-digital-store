<?php

namespace App\Domains\Orders\DTOs;

use App\Domains\Identity\Models\User;
use LogicException;

/**
 * Who a cart belongs to: a signed-in customer, or a guest holding a random cart token. Only
 * the token's SHA-256 hash is ever stored.
 */
final readonly class CartOwner
{
    private function __construct(
        public ?User $user,
        public ?string $guestToken,
    ) {}

    public static function user(User $user, ?string $guestToken = null): self
    {
        return new self($user, $guestToken);
    }

    public static function guest(?string $guestToken): self
    {
        return new self(null, $guestToken);
    }

    public function isGuest(): bool
    {
        return $this->user === null;
    }

    public function guestTokenHash(): ?string
    {
        return $this->guestToken === null ? null : hash('sha256', $this->guestToken);
    }

    /**
     * Stable identifier for idempotency scoping; a guest without a token has no cart to act on.
     */
    public function key(): string
    {
        if ($this->user !== null) {
            return 'user:'.$this->user->getKey();
        }

        if ($this->guestToken === null) {
            throw new LogicException('A guest owner needs a cart token.');
        }

        return 'guest:'.$this->guestTokenHash();
    }
}
