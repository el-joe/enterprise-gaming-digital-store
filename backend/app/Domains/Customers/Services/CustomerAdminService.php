<?php

namespace App\Domains\Customers\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Customers\Enums\CustomerRiskLevel;
use App\Domains\Customers\Models\CustomerNote;
use App\Domains\Customers\Models\CustomerProfile;
use App\Domains\Identity\DTOs\AccountDevice;
use App\Domains\Identity\Enums\BiometricKeyRevocationReason;
use App\Domains\Identity\Enums\TokenRevocationReason;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\MobileBiometricKey;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\AccountSessionService;
use App\Domains\Identity\Services\BiometricAuthService;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Staff actions on a customer account (M15, spec Phase 9.5): block and unblock (a blocked
 * customer is signed out everywhere and cannot sign in, check out or use the wallet), risk
 * level, internal notes, and revoking a device or a biometric key (OD-1). Every action is
 * audited with the staff member as actor and the written reason.
 */
class CustomerAdminService
{
    public function __construct(
        private AuditLogger $audit,
        private AccountSessionService $sessions,
        private BiometricAuthService $biometrics,
    ) {}

    /**
     * @return list<AccountDevice>
     */
    public function devices(User $customer): array
    {
        $this->ensureCustomer($customer);

        return $this->sessions->devicesForStaff($customer);
    }

    public function block(User $customer, string $reason): User
    {
        $this->ensureCustomer($customer);

        if ($customer->status === UserStatus::Suspended) {
            throw new ApplicationException('This customer is already blocked.', 'conflict', 409);
        }

        DB::transaction(function () use ($customer, $reason): void {
            $before = $customer->status?->value;
            $customer->forceFill(['status' => UserStatus::Suspended])->save();

            $this->audit->record(new AuditEntry('customers.blocked', $customer, ['status' => $before], ['status' => UserStatus::Suspended->value], $reason));
        });

        $signedOut = $this->sessions->signOutEverywhere($customer, TokenRevocationReason::AccountInactive);
        $this->biometrics->revokeAllForUser($customer, BiometricKeyRevocationReason::RevokedByStaff);
        $this->audit->record(new AuditEntry('customers.signed_out', $customer, after: $signedOut));

        return $customer->refresh();
    }

    public function unblock(User $customer, string $reason): User
    {
        $this->ensureCustomer($customer);

        if ($customer->status !== UserStatus::Suspended) {
            throw new ApplicationException('Only a blocked customer can be unblocked.', 'conflict', 409);
        }

        DB::transaction(function () use ($customer, $reason): void {
            $customer->forceFill(['status' => UserStatus::Active])->save();
            $this->audit->record(new AuditEntry('customers.unblocked', $customer, ['status' => UserStatus::Suspended->value], ['status' => UserStatus::Active->value], $reason));
        });

        return $customer->refresh();
    }

    public function setRisk(User $customer, CustomerRiskLevel $level, ?string $notes, string $reason): CustomerProfile
    {
        $this->ensureCustomer($customer);

        return DB::transaction(function () use ($customer, $level, $notes, $reason): CustomerProfile {
            $profile = CustomerProfile::query()->where('user_id', $customer->id)->lockForUpdate()->first()
                ?? new CustomerProfile(['risk_level' => CustomerRiskLevel::Low]);
            $before = ['risk_level' => $profile->risk_level?->value];

            $profile->forceFill(['user_id' => $customer->id, 'risk_level' => $level, 'risk_notes' => $notes])->save();
            $this->audit->record(new AuditEntry('customers.risk_level.changed', $customer, $before, ['risk_level' => $level->value], $reason));

            return $profile;
        });
    }

    public function addNote(User $customer, User $author, string $body): CustomerNote
    {
        $this->ensureCustomer($customer);

        return DB::transaction(function () use ($customer, $author, $body): CustomerNote {
            $note = new CustomerNote(['author_id' => $author->id, 'body' => $body]);
            $note->forceFill(['user_id' => $customer->id])->save();
            $this->audit->record(new AuditEntry('customers.note_added', $customer, after: ['note_id' => $note->id]));

            return $note;
        });
    }

    /**
     * Sign one device of the customer out (a browser session `web_...` or an app sign-in family).
     *
     * @throws ModelNotFoundException
     */
    public function revokeDevice(User $customer, string $deviceId, string $reason): void
    {
        $this->ensureCustomer($customer);

        $type = $this->sessions->revokeByStaff($customer, $deviceId);

        $this->audit->record(new AuditEntry('customers.device_revoked', $customer, after: ['type' => $type->value], reason: $reason));
    }

    /**
     * Revoke one biometric unlock key of the customer (OD-1, ADR-040).
     *
     * @throws ModelNotFoundException
     */
    public function revokeBiometricKey(User $customer, string $keyId, string $reason): void
    {
        $this->ensureCustomer($customer);

        $key = MobileBiometricKey::query()->where('user_id', $customer->id)->where('public_id', $keyId)->whereNull('revoked_at')->firstOrFail();
        $key->forceFill(['revoked_at' => now(), 'revocation_reason' => BiometricKeyRevocationReason::RevokedByStaff->value])->save();

        $this->audit->record(new AuditEntry('customers.biometric_key_revoked', $customer, after: ['key' => $key->public_id, 'family_id' => $key->family_id], reason: $reason));
    }

    private function ensureCustomer(User $customer): void
    {
        if ($customer->isStaff()) {
            throw (new ModelNotFoundException)->setModel(User::class);
        }
    }
}
