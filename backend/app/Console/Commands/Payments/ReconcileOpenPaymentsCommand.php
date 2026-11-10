<?php

namespace App\Console\Commands\Payments;

use App\Domains\Payments\Services\PaymentReconciler;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('payments:reconcile-open')]
#[Description('Re-read open payment attempts and refunds from the provider and retry failed webhooks')]
class ReconcileOpenPaymentsCommand extends Command
{
    public function handle(PaymentReconciler $reconciler): int
    {
        $counts = $reconciler->reconcileOpen();
        $this->line("payments: {$counts['payments']}, expired: {$counts['expired']}, refunds: {$counts['refunds']}, webhooks: {$counts['webhooks']}");

        return self::SUCCESS;
    }
}
