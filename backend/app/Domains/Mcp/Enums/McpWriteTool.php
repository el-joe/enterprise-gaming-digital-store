<?php

namespace App\Domains\Mcp\Enums;

use App\Domains\Identity\Enums\PermissionName;

/**
 * The MCP write tools (MCP_SERVER.md section 4). Each needs its scope on the token, an account
 * that is not read-only, and approval of the pending action before anything changes. When staff
 * approve, they must also hold the permission the equivalent admin action requires.
 */
enum McpWriteTool: string
{
    case UpdateProductPrice = 'update_product_price';
    case ActivateProduct = 'activate_product';
    case DeactivateProduct = 'deactivate_product';
    case CreateCoupon = 'create_coupon';
    case TriggerCatalogSync = 'trigger_catalog_sync';
    case RetryVerifiedFailedOrder = 'retry_verified_failed_order';

    public function scope(): McpScope
    {
        return match ($this) {
            self::UpdateProductPrice, self::ActivateProduct, self::DeactivateProduct => McpScope::ProductsWrite,
            self::CreateCoupon => McpScope::CouponsWrite,
            self::TriggerCatalogSync => McpScope::CatalogSync,
            self::RetryVerifiedFailedOrder => McpScope::OrdersRetry,
        };
    }

    public function staffPermission(): PermissionName
    {
        return match ($this) {
            self::UpdateProductPrice => PermissionName::PricingManage,
            self::ActivateProduct, self::DeactivateProduct => PermissionName::CatalogPublish,
            self::CreateCoupon => PermissionName::MarketingCoupons,
            self::TriggerCatalogSync => PermissionName::SuppliersSync,
            self::RetryVerifiedFailedOrder => PermissionName::OrdersReview,
        };
    }
}
