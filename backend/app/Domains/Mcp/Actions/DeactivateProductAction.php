<?php

namespace App\Domains\Mcp\Actions;

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Mcp\Enums\McpWriteTool;

class DeactivateProductAction extends ChangeProductStatusAction
{
    public function tool(): McpWriteTool
    {
        return McpWriteTool::DeactivateProduct;
    }

    protected function target(): ProductStatus
    {
        return ProductStatus::Inactive;
    }
}
