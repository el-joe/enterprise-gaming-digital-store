<?php

namespace App\Console\Commands\Payments;

use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Services\PaymentGatewayRegistry;
use App\Domains\Payments\Services\PaymentReconciler;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('payments:reconcile-daily')]
#[Description('Compare settled payments of the last 48 hours with each provider')]
class ReconcileDailyPaymentsCommand extends Command
{
    public function handle(PaymentReconciler $reconciler, PaymentGatewayRegistry $registry): int
    {
        $windowEnd = CarbonImmutable::now()->startOfHour();

        foreach (PaymentGateway::query()->orderBy('id')->get() as $gateway) {
            if (! $registry->has($gateway->adapter_key)) {
                continue;
            }

            $run = $reconciler->reconcileDaily($gateway, $windowEnd);
            $this->line("{$gateway->key}: ".($run === null ? 'skipped' : "{$run->status->value}, {$run->checked} checked, {$run->mismatches} mismatches"));
        }

        return self::SUCCESS;
    }
}
