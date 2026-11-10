<?php

namespace App\Http\Controllers\Automation;

use App\Domains\Automation\Services\CustomerMessagingService;
use App\Http\Requests\Automation\LogCustomerMessageRequest;
use App\Http\Requests\Automation\PrepareCustomerMessageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Secondary-channel customer messaging (D workflows). Consent and preferences are checked here,
 * never in n8n.
 */
class CustomerMessagingController extends AutomationController
{
    public function __construct(private CustomerMessagingService $messaging) {}

    public function prepare(PrepareCustomerMessageRequest $request): JsonResponse
    {
        return $this->success($this->messaging->prepare($this->caller($request), (string) $request->validated('event_id')));
    }

    public function segment(Request $request, string $segment): JsonResponse
    {
        return $this->success($this->messaging->segment($this->caller($request), $segment));
    }

    public function log(LogCustomerMessageRequest $request): JsonResponse
    {
        /** @var array{dedupe_key: string, customer_id: string, status: string} $input */
        $input = $request->validated();
        $result = $this->messaging->log($this->caller($request), $input);

        return $this->success($result, $result['repeated'] ? 'Already logged.' : 'Logged.', status: $result['repeated'] ? 200 : 201);
    }
}
