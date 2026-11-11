<?php

namespace App\Http\Controllers\Admin\Customers;

use App\Domains\Customers\Enums\CustomerRiskLevel;
use App\Domains\Customers\Services\CustomerAdminService;
use App\Domains\Customers\Services\CustomerPrivacyService;
use App\Domains\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Customers\AddCustomerNoteRequest;
use App\Http\Requests\Admin\Customers\AnonymizeCustomerRequest;
use App\Http\Requests\Admin\Customers\CustomerReasonRequest;
use App\Http\Requests\Admin\Customers\SetRiskRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Staff actions on customers (M15, spec Phase 9.5): block, unblock, risk, notes, device and
 * biometric key revocation, personal data export and erasure. All behind `customers.manage`
 * and audited with the reason.
 */
class CustomerController extends Controller
{
    public function __construct(private CustomerAdminService $customers) {}

    public function block(CustomerReasonRequest $request, User $customer): JsonResponse
    {
        $this->customers->block($customer, (string) $request->validated('reason'));

        return $this->success(['status' => 'suspended'], 'Customer blocked and signed out everywhere.');
    }

    public function unblock(CustomerReasonRequest $request, User $customer): JsonResponse
    {
        $this->customers->unblock($customer, (string) $request->validated('reason'));

        return $this->success(['status' => 'active'], 'Customer unblocked.');
    }

    public function risk(SetRiskRequest $request, User $customer): JsonResponse
    {
        $profile = $this->customers->setRisk($customer, CustomerRiskLevel::from((string) $request->validated('risk_level')), $request->validated('risk_notes'), (string) $request->validated('reason'));

        return $this->success(['risk_level' => $profile->risk_level?->value], 'Risk level saved.');
    }

    public function note(AddCustomerNoteRequest $request, User $customer): JsonResponse
    {
        $note = $this->customers->addNote($customer, $request->user(), (string) $request->validated('body'));

        return $this->success(['id' => $note->id], 'Note added.', status: 201);
    }

    public function revokeDevice(CustomerReasonRequest $request, User $customer, string $device): JsonResponse
    {
        $this->customers->revokeDevice($customer, $device, (string) $request->validated('reason'));

        return $this->success(message: 'Device signed out.');
    }

    public function revokeBiometricKey(CustomerReasonRequest $request, User $customer, string $key): JsonResponse
    {
        $this->customers->revokeBiometricKey($customer, $key, (string) $request->validated('reason'));

        return $this->success(message: 'Biometric key revoked.');
    }

    public function export(User $customer, CustomerPrivacyService $privacy): Response
    {
        $data = $privacy->export($customer);

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="customer-'.$customer->public_id.'.json"',
            'Cache-Control' => 'no-store, private',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function anonymize(AnonymizeCustomerRequest $request, User $customer, CustomerPrivacyService $privacy): JsonResponse
    {
        $privacy->anonymize($customer, (string) $request->validated('reason'));

        return $this->success(message: 'Customer erased. Financial records are kept.');
    }
}
