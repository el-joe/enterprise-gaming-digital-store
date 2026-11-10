<?php

namespace App\Domains\Mcp\Services;

use App\Domains\Mcp\Actions\ActivateProductAction;
use App\Domains\Mcp\Actions\CreateCouponAction;
use App\Domains\Mcp\Actions\DeactivateProductAction;
use App\Domains\Mcp\Actions\RetryVerifiedFailedOrderAction;
use App\Domains\Mcp\Actions\TriggerCatalogSyncAction;
use App\Domains\Mcp\Actions\UpdateProductPriceAction;
use App\Domains\Mcp\Contracts\McpWriteAction;
use App\Domains\Mcp\Enums\McpWriteTool;
use Illuminate\Contracts\Container\Container;

/**
 * Maps each MCP write tool to its action. There is no generic or dynamic action: a tool that is
 * not listed here cannot be requested.
 */
class McpActionRegistry
{
    private const array ACTIONS = [
        'update_product_price' => UpdateProductPriceAction::class,
        'activate_product' => ActivateProductAction::class,
        'deactivate_product' => DeactivateProductAction::class,
        'create_coupon' => CreateCouponAction::class,
        'trigger_catalog_sync' => TriggerCatalogSyncAction::class,
        'retry_verified_failed_order' => RetryVerifiedFailedOrderAction::class,
    ];

    public function __construct(private Container $container) {}

    public function for(McpWriteTool $tool): McpWriteAction
    {
        return $this->container->make(self::ACTIONS[$tool->value]);
    }
}
