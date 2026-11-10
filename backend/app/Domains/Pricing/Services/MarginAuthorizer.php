<?php

namespace App\Domains\Pricing\Services;

use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Identity\Models\User;
use App\Domains\Pricing\Exceptions\MarginGuardRefused;
use App\Domains\Shared\Exceptions\ApplicationException;

/**
 * The one place that decides whether staff may price below the margin floor.
 */
class MarginAuthorizer
{
    /**
     * @throws ApplicationException when the actor lacks the permission
     * @throws MarginGuardRefused when no reason is given
     */
    public function assertAuthorized(?User $actor, ?string $reason, string $field, string $message): void
    {
        if ($actor === null || ! $actor->checkPermissionTo(PermissionName::PricingOverrideMargin->value)) {
            throw new ApplicationException($message, 'margin_override_forbidden', 403);
        }

        if ($reason === null || trim($reason) === '') {
            throw new MarginGuardRefused($message, [$field => ['A reason is required to override the margin floor.']]);
        }
    }
}
