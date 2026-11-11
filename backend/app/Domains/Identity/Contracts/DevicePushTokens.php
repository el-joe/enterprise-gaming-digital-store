<?php

namespace App\Domains\Identity\Contracts;

/**
 * Push tokens tied to a mobile sign-in (ADR-042). Identity revokes sign-ins and must drop their
 * push tokens with them; the tokens belong to the Notifications domain, which implements this
 * contract, so Identity never reaches into another domain's repositories.
 */
interface DevicePushTokens
{
    /**
     * Delete the push tokens of these sign-ins (refresh-token families).
     *
     * @param  iterable<string>  $familyIds
     */
    public function deleteForFamilies(iterable $familyIds): int;

    /**
     * Delete every push token of the customer except the one of the kept sign-in (when given).
     */
    public function deleteForUserExcept(int $userId, ?string $keptFamilyId): int;

    /**
     * Delete tokens whose sign-in has no live refresh token any more.
     */
    public function pruneOrphaned(): int;
}
