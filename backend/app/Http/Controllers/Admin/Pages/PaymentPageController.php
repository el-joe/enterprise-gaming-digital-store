<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Services\PaymentGatewayAdminService;
use App\Domains\Payments\Services\PaymentPublicCredentials;
use App\Http\Controllers\Controller;
use App\Http\Support\AdminTablePage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Payments (M14, spec Phase 9.6): gateways with their mode, write-only credentials, charge
 * currency (fixed per gateway, OD-1) and eligibility rules; then one tab per table.
 */
class PaymentPageController extends Controller
{
    public function gateways(PaymentGatewayAdminService $service): View
    {
        $gateways = $service->gateways();

        return view('admin.payments.gateways', [
            'gateways' => $gateways,
            'credentials' => $gateways->mapWithKeys(static fn (PaymentGateway $gateway): array => [$gateway->id => $service->credentialState($gateway)]),
        ]);
    }

    public function gateway(PaymentGateway $gateway, PaymentGatewayAdminService $service, PaymentPublicCredentials $public): Response
    {
        return response()->view('admin.payments.gateway', [
            'public' => $public->forGateway($gateway),
            'gateway' => $service->gateway($gateway),
            'credentials' => $service->credentialState($gateway),
            'fields' => (array) config("admin.credential_fields.gateways.{$gateway->adapter_key}", []),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function transactions(Request $request, AdminTablePage $tables): View
    {
        return $this->tab('transactions', $tables->make('payments', $request->user()));
    }

    public function webhooks(Request $request, AdminTablePage $tables): View
    {
        return $this->tab('webhooks', $tables->make('webhooks', $request->user()));
    }

    public function refunds(Request $request, AdminTablePage $tables): View
    {
        return $this->tab('refunds', $tables->make('refunds', $request->user()));
    }

    public function disputes(Request $request, AdminTablePage $tables): View
    {
        return $this->tab('disputes', $tables->make('disputes', $request->user()));
    }

    public function reconciliation(Request $request, AdminTablePage $tables): View
    {
        return $this->tab('reconciliation', $tables->make('reconciliation', $request->user()));
    }

    /**
     * @param  array<string, mixed>  $table
     */
    private function tab(string $tab, array $table): View
    {
        return view('admin.payments.table', ['tab' => $tab, 'table' => $table]);
    }
}
