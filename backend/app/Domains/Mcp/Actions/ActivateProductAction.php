<?php

namespace App\Domains\Mcp\Actions;

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Mcp\Enums\McpWriteTool;

class ActivateProductAction extends ChangeProductStatusAction
{
    public function tool(): McpWriteTool
    {
        return McpWriteTool::ActivateProduct;
    }

    protected function target(): ProductStatus
    {
        return ProductStatus::Active;
    }
}
