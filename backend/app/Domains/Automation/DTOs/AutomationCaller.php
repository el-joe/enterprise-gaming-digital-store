<?php

namespace App\Domains\Automation\DTOs;

use App\Domains\Automation\Enums\AutomationScope;
use App\Domains\Identity\Models\ServiceAccount;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * The authenticated n8n service account, the exact token used, and the workflow and execution the
 * signed request belongs to.
 */
final readonly class AutomationCaller
{
    public const string REQUEST_ATTRIBUTE = 'automation.caller';

    /**
     * @param  list<AutomationScope>  $scopes
     */
    public function __construct(
        public ServiceAccount $account,
        public PersonalAccessToken $token,
        public array $scopes,
        public string $workflowId,
        public string $executionKey,
    ) {}

    public function has(AutomationScope $scope): bool
    {
        return in_array($scope, $this->scopes, true);
    }
}
