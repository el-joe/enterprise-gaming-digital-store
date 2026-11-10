<?php

namespace App\Http\Controllers\Account;

use App\Domains\Wallet\Services\WalletService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\PageRequest;
use App\Http\Resources\Account\WalletResource;
use App\Http\Resources\Account\WalletTransactionResource;
use Illuminate\Http\JsonResponse;

class WalletController extends Controller
{
    public function __construct(private WalletService $wallets) {}

    public function show(PageRequest $request): JsonResponse
    {
        return $this->success(['wallet' => WalletResource::make($this->wallets->summary($request->user()))->resolve()]);
    }

    public function transactions(PageRequest $request): JsonResponse
    {
        return $this->paginated($this->wallets->statement($request->user(), $request->page(), $request->perPage()), WalletTransactionResource::class);
    }
}
