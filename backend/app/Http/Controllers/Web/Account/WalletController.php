<?php

namespace App\Http\Controllers\Web\Account;

use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Wallet\Models\WalletTransaction;
use App\Domains\Wallet\Services\WalletService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Account\AccountPageRequest;
use App\Http\Resources\Account\WalletResource;
use Illuminate\View\View;

/**
 * Wallet (`/account/wallet`, M13): balance and statement. Store credit from refunds and staff
 * credits only (BD3), no top-up. Amounts are formatted on the server in the wallet currency.
 */
class WalletController extends Controller
{
    public function show(AccountPageRequest $request, WalletService $wallets, CurrencyConverter $converter): View
    {
        $wallet = $wallets->summary($request->user());
        $page = $wallets->statement($request->user(), $request->page(), AccountPageRequest::PER_PAGE)->withPath($request->url());

        $transactions = collect($page->items())->map(static function (WalletTransaction $transaction) use ($converter, $wallet): array {
            $amount = (string) $transaction->amount;
            $credit = ! str_starts_with($amount, '-');

            return [
                'id' => $transaction->id,
                'type' => $transaction->type->value,
                'credit' => $credit,
                'amount' => $converter->present(Money::of(ltrim($amount, '-'), $wallet->currency))['formatted'],
                'balance_after' => $converter->present(Money::of((string) $transaction->balance_after, $wallet->currency))['formatted'],
                'note' => $transaction->note,
                'created_at' => $transaction->created_at?->toIso8601String(),
            ];
        })->all();

        return view('account.wallet', [
            'wallet' => (new WalletResource($wallet))->resolve($request),
            'transactions' => $transactions,
            'paginator' => $page,
        ]);
    }
}
