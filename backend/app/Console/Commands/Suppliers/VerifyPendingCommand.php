<?php

namespace App\Console\Commands\Suppliers;

use App\Domains\Fulfillment\Jobs\VerifySupplierTransaction;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('suppliers:verify-pending {--limit=500}')]
#[Description('Queue status lookups for supplier attempts that are due (idx_st_verify)')]
class VerifyPendingCommand extends Command
{
    public function handle(): int
    {
        $ids = SupplierTransaction::query()
            ->whereIn('status', [SupplierTransactionStatus::PendingVerification, SupplierTransactionStatus::Accepted])
            ->whereNotNull('next_verification_at')
            ->where('next_verification_at', '<=', now())
            ->orderBy('next_verification_at')
            ->limit((int) $this->option('limit'))
            ->pluck('id');

        $ids->each(static fn (int $id) => VerifySupplierTransaction::dispatch($id));
        $this->info("Queued {$ids->count()} verifications.");

        return self::SUCCESS;
    }
}
