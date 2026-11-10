<?php

namespace App\Domains\Mcp\Actions;

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Services\ProductPublicationService;
use App\Domains\Identity\Models\User;
use App\Domains\Mcp\Contracts\McpWriteAction;
use App\Domains\Mcp\DTOs\PreparedAction;
use App\Domains\Mcp\Repositories\McpCatalogRepository;
use App\Domains\Shared\Exceptions\ApplicationException;

/**
 * Shared by activate_product and deactivate_product. Publication goes through
 * ProductPublicationService, which refuses a product customers could not buy.
 */
abstract class ChangeProductStatusAction implements McpWriteAction
{
    public function __construct(protected McpCatalogRepository $catalog, protected ProductPublicationService $publication) {}

    abstract protected function target(): ProductStatus;

    public function rules(): array
    {
        return [
            'product_sku' => ['required', 'string', 'max:64'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function prepare(array $validated): PreparedAction
    {
        $product = $this->product((string) $validated['product_sku']);

        if ($product->status === $this->target()) {
            throw new ApplicationException("The product is already {$this->target()->value}.", 'no_change', 409);
        }

        $verb = $this->target() === ProductStatus::Active ? 'Publish' : 'Unpublish';

        return new PreparedAction(
            ['product_sku' => $product->sku, 'reason' => (string) $validated['reason']],
            1,
            "{$verb} product {$product->sku} ({$product->name}); it is {$product->status->value} now.",
        );
    }

    public function execute(array $arguments, ?User $approver, int $serviceAccountId): array
    {
        $product = $this->product((string) $arguments['product_sku']);
        $previous = $product->status;

        $product = $this->target() === ProductStatus::Active
            ? $this->publication->activate($product)
            : $this->publication->deactivate($product);

        return ['product_sku' => $product->sku, 'previous_status' => $previous->value, 'status' => $product->status->value];
    }

    private function product(string $sku): Product
    {
        return $this->catalog->findProductBySku($sku)
            ?? throw new ApplicationException('No product has this SKU.', 'not_found', 404);
    }
}
