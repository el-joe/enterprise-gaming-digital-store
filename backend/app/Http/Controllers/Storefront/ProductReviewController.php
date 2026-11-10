<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\Product;
use App\Domains\Reviews\Repositories\ReviewRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\PageRequest;
use App\Http\Resources\Storefront\ProductReviewResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class ProductReviewController extends Controller
{
    /**
     * Approved reviews of an active product, with its rating summary.
     */
    public function index(PageRequest $request, Product $product, ReviewRepository $reviews): JsonResponse
    {
        if ($product->status !== ProductStatus::Active) {
            throw (new ModelNotFoundException)->setModel(Product::class);
        }

        return $this->paginated($reviews->approvedFor($product->id, $request->page(), $request->perPage()), ProductReviewResource::class, [
            'rating_avg' => (string) $product->rating_avg,
            'rating_count' => (int) $product->rating_count,
        ]);
    }
}
