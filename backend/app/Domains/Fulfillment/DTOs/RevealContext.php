<?php

namespace App\Domains\Fulfillment\DTOs;

use App\Domains\Fulfillment\Enums\DeviceIntegrity;
use App\Domains\Fulfillment\Enums\DigitalCodeAccessActorType;
use App\Domains\Fulfillment\Enums\DigitalCodeAccessPurpose;
use App\Domains\Identity\Models\User;

/**
 * Who asks to see a code, and why. `reason` is mandatory for staff. `sessionVerified` is the
 * caller's proof of fresh identity: a customer's password confirmed within
 * `fulfillment.reveal_recent_auth_minutes`, or a guest session holding both the signed link and
 * the email one-time code for this order. `deviceIntegrity` is the app's advisory verdict
 * (`X-Device-Integrity`, OD-1), audited and never used to refuse.
 */
final readonly class RevealContext
{
    public function __construct(
        public DigitalCodeAccessActorType $actorType,
        public ?User $user,
        public DigitalCodeAccessPurpose $purpose,
        public ?string $reason = null,
        public ?string $ip = null,
        public ?string $userAgent = null,
        public bool $sessionVerified = false,
        public ?DeviceIntegrity $deviceIntegrity = null,
    ) {}
}
