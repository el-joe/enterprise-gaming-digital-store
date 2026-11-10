<?php

namespace App\Http\Controllers\Admin\Fulfillment;

use App\Domains\Fulfillment\DTOs\RevealContext;
use App\Domains\Fulfillment\Enums\DigitalCodeAccessActorType;
use App\Domains\Fulfillment\Enums\DigitalCodeAccessPurpose;
use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Fulfillment\Services\CodeRevealService;
use App\Domains\Fulfillment\Services\DigitalCodeImporter;
use App\Http\Controllers\Account\OrderCodeController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Fulfillment\ImportDigitalCodesRequest;
use App\Http\Requests\Admin\Fulfillment\ReasonRequest;
use Illuminate\Http\JsonResponse;

class DigitalCodeController extends Controller
{
    public function import(ImportDigitalCodesRequest $request, DigitalCodeImporter $importer): JsonResponse
    {
        return $this->success($importer->import((int) $request->validated('product_variant_id'), $request->validated('codes'), $request->user()), 'Codes imported.', status: 201);
    }

    public function reveal(ReasonRequest $request, DigitalCode $digitalCode, CodeRevealService $reveals): JsonResponse
    {
        $revealed = $reveals->revealForStaff($digitalCode, new RevealContext(
            DigitalCodeAccessActorType::Staff,
            $request->user(),
            DigitalCodeAccessPurpose::SupportVerification,
            $request->validated('reason'),
            $request->ip(),
            $request->userAgent(),
        ));

        return OrderCodeController::revealResponse($this, $revealed, null);
    }
}
