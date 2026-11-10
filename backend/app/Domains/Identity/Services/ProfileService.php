<?php

namespace App\Domains\Identity\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Enums\AuditLogActorType;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\DTOs\ProfileChanges;
use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The customer's own profile (M-A5, gap G5): name, locale, display currency, country and phone.
 * The email address is not changed here (no verified change flow exists). Every change is
 * audited with the changed field names; the phone number itself is never written to the log.
 */
class ProfileService
{
    private const array LOGGED_VALUES = ['locale', 'display_currency', 'country_code'];

    public function __construct(private AuditLogger $audit) {}

    public function update(User $user, ProfileChanges $changes): User
    {
        return DB::transaction(function () use ($user, $changes): User {
            $user->fill($changes->attributes);
            $changed = array_keys($user->getDirty());

            if ($changed === []) {
                return $user;
            }

            $before = array_intersect_key($user->getOriginal(), array_flip(array_intersect($changed, self::LOGGED_VALUES)));
            $user->save();

            $this->audit->record(new AuditEntry(
                action: 'account.profile_updated',
                subject: $user,
                before: $before,
                after: ['fields' => $changed, ...array_intersect_key($user->only(self::LOGGED_VALUES), $before)],
                actorType: AuditLogActorType::Customer,
                actorId: $user->id,
            ));

            return $user;
        });
    }
}
