<?php

namespace App\Domains\Catalog\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Product;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Facades\DB;

/**
 * Owns products.status. A product can only be published when customers could actually buy it.
 */
class ProductPublicationService
{
    private const array TYPES_NEEDING_CUSTOMER_INPUT = [DeliveryType::DirectTopup, DeliveryType::AccountRecharge];

    public function __construct(
        private AuditLogger $audit,
        private SearchIndexer $search,
        private ProductService $products,
    ) {}

    public function activate(Product $product): Product
    {
        $this->assertPublishable($product);

        return $this->transition($product, ProductStatus::Active);
    }

    public function deactivate(Product $product): Product
    {
        return $this->transition($product, ProductStatus::Inactive);
    }

    /**
     * Apply one action to many products; a failure on one does not stop the others.
     *
     * @param  list<string>  $publicIds
     * @return array{processed: int, failed: list<array{id: string, name: ?string, error: string, reasons: list<string>}>}
     */
    public function bulk(string $action, array $publicIds): array
    {
        $products = Product::query()->whereIn('public_id', $publicIds)->get()->keyBy('public_id');

        $processed = 0;
        $failed = [];

        foreach ($publicIds as $publicId) {
            $product = $products->get($publicId);

            if ($product === null) {
                $failed[] = ['id' => $publicId, 'name' => null, 'error' => 'Product not found.', 'reasons' => [__('admin.products.bulk_not_found')]];

                continue;
            }

            try {
                match ($action) {
                    'activate' => $this->activate($product),
                    'deactivate' => $this->deactivate($product),
                    'delete' => $this->products->delete($product),
                };

                $processed++;
            } catch (ApplicationException $exception) {
                $reasons = array_values(array_merge(...array_values($exception->errors ?? [])));
                $failed[] = [
                    'id' => $publicId,
                    'name' => $product->name,
                    'error' => $exception->getMessage(),
                    'reasons' => $reasons !== [] ? $reasons : [$exception->localizedMessage()],
                ];
            }
        }

        return ['processed' => $processed, 'failed' => $failed];
    }

    private function transition(Product $product, ProductStatus $status): Product
    {
        DB::transaction(function () use ($product, $status): void {
            $previous = $product->status;

            $product->forceFill([
                'status' => $status,
                'published_at' => $status === ProductStatus::Active ? ($product->published_at ?? now()) : $product->published_at,
            ])->save();

            $this->audit->record(new AuditEntry(
                action: 'catalog.product.status_changed',
                subject: $product,
                before: ['status' => $previous->value],
                after: ['status' => $status->value],
            ));

            $this->search->indexProduct($product);
        });

        return $this->products->withRelations($product->refresh());
    }

    private function assertPublishable(Product $product): void
    {
        $errors = [];

        if (! $product->productVariants()->where('is_active', true)->exists()) {
            $onlyCustomAmounts = $product->productVariants()->exists()
                && ! $product->productVariants()->where('is_custom_amount', false)->exists();

            // Custom (range) amounts can't be priced yet, so their variants stay inactive.
            $errors['variants'] = [$onlyCustomAmounts
                ? __('admin.products.publish_custom_amount_only')
                : __('admin.products.publish_needs_active_variant')];
        }

        if (in_array($product->delivery_type, self::TYPES_NEEDING_CUSTOMER_INPUT, true)
            && ! $product->productDeliveryFields()->where('is_required', true)->exists()) {
            $errors['delivery_fields'] = [__('admin.products.publish_needs_delivery_field')];
        }

        if ($errors !== []) {
            throw new ApplicationException('This product cannot be published yet.', 'product_not_publishable', 422, $errors);
        }
    }
}
