<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Domains\Customers\Repositories\CustomerAdminRepository;
use App\Domains\Customers\Services\CustomerAdminService;
use App\Domains\Identity\Models\User;
use App\Domains\Mcp\Services\PiiMasker;
use App\Domains\Wallet\Services\WalletService;
use App\Http\Controllers\Controller;
use App\Http\Support\AdminTablePage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Customers (M15, spec Phase 9.5, Figma "Admin / Customers / List" and "Detail"): list, and a
 * detail page with orders, wallet ledger and adjustment, risk, devices and biometric keys
 * (OD-1), notes, tickets, audit trail and the privacy actions. Contact details are masked
 * without `customers.view_pii`; every action is its own audited JSON route.
 */
class CustomerPageController extends Controller
{
    public function index(Request $request, AdminTablePage $tables): View
    {
        return view('admin.customers.index', ['table' => $tables->make('customers', $request->user())]);
    }

    public function show(
        Request $request,
        User $customer,
        CustomerAdminRepository $reader,
        CustomerAdminService $customers,
        WalletService $wallets,
        AdminTablePage $tables,
        PiiMasker $masker,
    ): Response {
        if ($customer->isStaff()) {
            throw new NotFoundHttpException;
        }

        $viewer = $request->user();
        $customer->loadMissing('customerProfile');
        $pii = $viewer->can('customers.view_pii');

        return response()->view('admin.customers.show', [
            'customer' => $customer,
            'contact' => [
                'email' => $pii ? $customer->email : $masker->email($customer->email),
                'phone' => $pii ? $customer->phone : $masker->phone($customer->phone),
            ],
            'profile' => $customer->customerProfile,
            'wallet' => $wallets->summary($customer),
            'orders' => $reader->recentOrders($customer),
            'ordersCount' => $reader->ordersCount($customer),
            'tickets' => $reader->recentTickets($customer),
            'reviewsCount' => $reader->reviewsCount($customer),
            'notes' => $reader->notes($customer),
            'devices' => $customers->devices($customer),
            'biometricKeys' => $reader->biometricKeys($customer),
            'audit' => $viewer->can('audit.view') ? $reader->auditTrail($customer) : null,
            'ledger' => $tables->make('customer-wallet', $viewer, ['customer' => (string) $customer->id]),
            'idempotencyKey' => 'admin-adjust:'.Str::uuid()->toString(),
        ])->header('Cache-Control', 'no-store, private');
    }
}
