<?php

namespace App\Domains\Mcp\Enums;

/**
 * MCP token scopes (MCP_SERVER.md section 2), stored as Sanctum abilities with the `mcp:` prefix.
 * Only these exact abilities are honoured; a wildcard ability grants nothing.
 */
enum McpScope: string
{
    case ProductsRead = 'products:read';
    case OrdersRead = 'orders:read';
    case AnalyticsRead = 'analytics:read';
    case SuppliersRead = 'suppliers:read';
    case MarketingRead = 'marketing:read';
    case OperationsRead = 'operations:read';
    case ProductsWrite = 'products:write';
    case CouponsWrite = 'coupons:write';
    case CatalogSync = 'catalog:sync';
    case OrdersRetry = 'orders:retry';
    case ActionsConfirm = 'actions:confirm';

    public const string ABILITY_PREFIX = 'mcp:';

    public function ability(): string
    {
        return self::ABILITY_PREFIX.$this->value;
    }

    /**
     * A scope that lets the account change data (as opposed to reading it).
     */
    public function isWrite(): bool
    {
        return in_array($this, self::writeScopes(), true);
    }

    /**
     * @return list<self>
     */
    public static function readScopes(): array
    {
        return [self::ProductsRead, self::OrdersRead, self::AnalyticsRead, self::SuppliersRead, self::MarketingRead, self::OperationsRead];
    }

    /**
     * @return list<self>
     */
    public static function writeScopes(): array
    {
        return [self::ProductsWrite, self::CouponsWrite, self::CatalogSync, self::OrdersRetry];
    }

    /**
     * The scopes held by a token, ignoring anything that is not an exact MCP ability.
     *
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
