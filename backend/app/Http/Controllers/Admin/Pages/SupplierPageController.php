<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Services\SupplierOverview;
use App\Http\Controllers\Controller;
use App\Http\Support\AdminTablePage;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Suppliers list and detail (M14, spec Phase 9.3): capability matrix, health and circuit
 * breaker, balance, fallback settings, write-only credentials, API logs and mappings.
 */
class SupplierPageController extends Controller
{
    public function index(Request $request, AdminTablePage $tables, SupplierOverview $overview): View
    {
        return view('admin.suppliers.index', [
            'table' => $tables->make('suppliers', $request->user()),
            'summary' => $overview->summary(),
        ]);
    }

    public function show(Request $request, Supplier $supplier, AdminTablePage $tables, SupplierOverview $overview): View
    {
        $user = $request->user();
        $context = ['supplier' => (string) $supplier->id];

        return view('admin.suppliers.show', [
            'detail' => $overview->detail($supplier),
            'logs' => $user->can('suppliers.logs') ? $tables->make('supplier-logs', $user, $context) : null,
            'mappings' => $tables->make('supplier-mappings', $user, $context),
        ]);
    }
}
