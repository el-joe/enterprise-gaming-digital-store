<?php

namespace App\Console\Commands\Wallet;

use App\Domains\Wallet\Models\CustomerWallet;
use App\Domains\Wallet\Repositories\WalletRepository;
use App\Domains\Wallet\Services\WalletLedger;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('wallet:verify-ledger')]
#[Description('Check that every wallet balance equals the sum of its ledger entries')]
class VerifyWalletLedgerCommand extends Command
{
    public function handle(WalletRepository $wallets, WalletLedger $ledger): int
    {
        $after = 0;
        $mismatches = 0;

        while (($ids = $wallets->walletIds($after, 500)) !== []) {
            foreach (CustomerWallet::query()->whereIn('id', $ids)->orderBy('id')->get() as $wallet) {
                if (! $ledger->isConsistent($wallet)) {
                    $mismatches++;
                    Log::alert('wallet.ledger_mismatch', ['customer_wallet_id' => $wallet->id]);
                }
            }

            $after = end($ids);
        }

        $this->line("{$mismatches} mismatched wallets");

        return $mismatches === 0 ? self::SUCCESS : self::FAILURE;
    }
}
