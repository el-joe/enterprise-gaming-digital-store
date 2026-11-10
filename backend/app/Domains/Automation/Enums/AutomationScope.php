<?php

namespace App\Domains\Automation\Enums;

/**
 * Automation token scopes, stored as Sanctum abilities with the `automation:` prefix. Only these
 * exact abilities are honoured; a wildcard ability grants nothing.
 */
enum AutomationScope: string
{
    case ExecutionsWrite = 'executions:write';
    case ReportsRead = 'reports:read';
    case MonitorRead = 'monitor:read';
    case MarketingRead = 'marketing:read';
    case JobsRun = 'jobs:run';
    case SuggestionsWrite = 'suggestions:write';
    case CustomersMessage = 'customers:message';

    public const string ABILITY_PREFIX = 'automation:';

    public function ability(): string
    {
        return self::ABILITY_PREFIX.$this->value;
    }

    /**
     * Scopes that start work, create records or expose customer contact details. They are granted
     * only when explicitly requested.
     */
    public function isPrivileged(): bool
    {
        return in_array($this, self::privilegedScopes(), true);
    }

    /**
     * @return list<self>
     */
    public static function defaultScopes(): array
    {
        return [self::ExecutionsWrite, self::ReportsRead, self::MonitorRead, self::MarketingRead];
    }

    /**
     * @return list<self>
     */
    public static function privilegedScopes(): array
    {
        return [self::JobsRun, self::SuggestionsWrite, self::CustomersMessage];
    }

    /**
     * @param  array<int, mixed>  $abilities
     * @return list<self>
     */
    public static function fromAbilities(array $abilities): array
    {
        $scopes = [];

        foreach ($abilities as $ability) {
            if (is_string($ability) && str_starts_with($ability, self::ABILITY_PREFIX)) {
                $scope = self::tryFrom(substr($ability, strlen(self::ABILITY_PREFIX)));

                if ($scope !== null) {
                    $scopes[] = $scope;
                }
            }
        }

        return $scopes;
    }
}
