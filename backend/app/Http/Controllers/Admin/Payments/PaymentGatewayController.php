<?php

namespace App\Http\Controllers\Admin\Payments;

use App\Domains\Payments\Enums\ReconciliationItemStatus;
use App\Domains\Payments\Models\PaymentEligibilityRule;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\ReconciliationItem;
use App\Domains\Payments\Services\PaymentGatewayAdminService;
use App\Domains\Payments\Services\PaymentPublicCredentials;
use App\Domains\Shared\Enums\EnvironmentMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Payments\ResolveReconciliationRequest;
use App\Http\Requests\Admin\Payments\SaveEligibilityRuleRequest;
use App\Http\Requests\Admin\Payments\SetGatewayCredentialsRequest;
use App\Http\Requests\Admin\Payments\SetPublicKeyRequest;
use App\Http\Requests\Admin\Payments\ToggleRuleRequest;
use App\Http\Requests\Admin\Payments\UpdateGatewayRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gateway configuration and reconciliation actions (M14). Credentials are write-only: the
 * response says which modes are configured, never a value.
 */
class PaymentGatewayController extends Controller
{
    public function __construct(private PaymentGatewayAdminService $service) {}

    public function update(UpdateGatewayRequest $request, PaymentGateway $gateway): JsonResponse
    {
        $gateway = $this->service->update($gateway, $request->validated());

        return $this->success(['id' => $gateway->id, 'is_active' => $gateway->is_active, 'mode' => $gateway->mode->value], 'Saved.');
    }

    public function credentials(SetGatewayCredentialsRequest $request, PaymentGateway $gateway): JsonResponse
    {
        $this->service->setCredentials($gateway, EnvironmentMode::from($request->validated('mode')), $request->credentials(), $request->validated('webhook_secret'));

        return $this->success(['credentials_configured' => $this->service->credentialState($gateway)], 'Credentials saved.');
    }

    public function toggleRule(ToggleRuleRequest $request, PaymentEligibilityRule $rule): JsonResponse
    {
        $rule = $this->service->setRuleActive($rule, (bool) $request->validated('is_active'));

        return $this->success(['id' => $rule->id, 'is_active' => $rule->is_active], 'Saved.');
    }

    public function reconcile(Request $request): JsonResponse
    {
        return $this->success($this->service->reconcileNow($request->user()), 'Reconciliation run.');
    }

    public function resolveItem(ResolveReconciliationRequest $request, ReconciliationItem $item): JsonResponse
    {
        $changed = $this->service->resolveItems([$item->id], ReconciliationItemStatus::from($request->validated('status')), $request->validated('note'), $request->user());

        return $this->success(['changed' => $changed], 'Saved.');
    }

    public function storeRule(SaveEligibilityRuleRequest $request, PaymentGateway $gateway): JsonResponse
    {
        $rule = $this->service->saveRule($gateway, null, $request->validated());

        return $this->success(['id' => $rule->id], 'Rule created.', status: 201);
    }

    public function updateRule(SaveEligibilityRuleRequest $request, PaymentEligibilityRule $rule): JsonResponse
    {
        $this->service->saveRule($rule->paymentGateway, $rule, $request->validated());

        return $this->success(['id' => $rule->id], 'Rule saved.');
    }

    public function destroyRule(PaymentEligibilityRule $rule): JsonResponse
    {
        $this->service->deleteRule($rule);

        return $this->success(message: 'Rule deleted.');
    }

    public function publicKey(SetPublicKeyRequest $request, PaymentGateway $gateway, PaymentPublicCredentials $public): JsonResponse
    {
        $value = $request->validated('value');
        $public->set($gateway->key, EnvironmentMode::from((string) $request->validated('mode')), $value === null || $value === '' ? null : (string) $value);

        return $this->success(message: 'Public key saved.');
    }
}
