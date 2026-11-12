<?php

namespace App\Http\Controllers\Admin\Suppliers;

use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Suppliers\Models\SupplierImageCandidate;
use App\Domains\Suppliers\Services\SupplierImageCandidates;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

/**
 * "Use this" in the Supplier images gallery of the product, platform and category edit pages:
 * the candidate becomes the active image, marked as chosen by staff (audited).
 */
class SupplierImageCandidateController extends Controller
{
    public function activate(SupplierImageCandidate $supplierImageCandidate, SupplierImageCandidates $candidates): JsonResponse
    {
        try {
            $candidates->activate($supplierImageCandidate);
        } catch (InvalidArgumentException) {
            $message = (string) __('admin.supplier_images.unavailable');

            throw new ApplicationException($message, 'invalid_state', 422);
        }

        return $this->success(message: (string) __('admin.supplier_images.activated'));
    }
}
