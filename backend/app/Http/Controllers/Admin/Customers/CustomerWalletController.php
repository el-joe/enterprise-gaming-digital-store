<?php

namespace App\Http\Controllers\Admin\Customers;

use App\Domains\Identity\Models\User;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Wallet\Enums\CustomerWalletStatus;
use App\Domains\Wallet\Services\WalletService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\PageRequest;
use App\Http\Requests\Admin\Customers\AdjustWalletRequest;
use App\Http\Requests\Admin\Customers\SetWalletStatusRequest;
use App\Http\Resources\Account\WalletResource;
use App\Http\Resources\Admin\Customers\WalletTransactionResource;
use Illuminate\Http\JsonResponse;

class CustomerWalletController extends Controller
{
    public function __construct(private WalletService $wallets) {}

    public function show(User $customer): JsonResponse
    {
        return $this->success(['wallet' => WalletResource::make($this->wallets->summary($customer))->resolve()]);
    }

    public function transactions(PageRequest $request, User $customer): JsonResponse
    {
        return $this->paginated($this->wallets->statement($customer, $request->page(), $request->perPage()), WalletTransactionResource::class);
    }

    public function adjust(AdjustWalletRequest $request, User $customer): JsonResponse
    {
        $wallet = $this->wallets->summary($customer);

        $transaction = $this->wallets->adjust(
            $customer,
            $request->user(),
            $request->validated('direction') === 'credit',
            Money::of($request->validated('amount'), $wallet->currency),
            $request->validated('reason'),
            $request->validated('idempotency_key'),
        );

        return $this->success([
            'transaction' => WalletTransactionResource::make($transaction)->resolve(),
            'wallet' => WalletResource::make($this->wallets->summary($customer))->resolve(),
        ], 'Wallet adjusted.', status: 201);
    }

    public function status(SetWalletStatusRequest $request, User $customer): JsonResponse
    {
        $wallet = $this->wallets->setStatus($customer, CustomerWalletStatus::from($request->validated('status')), $request->validated('reason'));

        return $this->success(['wallet' => WalletResource::make($wallet)->resolve()], 'Saved.');
    }
}
