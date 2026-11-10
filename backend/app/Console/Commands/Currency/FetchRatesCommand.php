<?php

namespace App\Console\Commands\Currency;

use App\Domains\Currency\Exceptions\ExchangeRateProviderFailed;
use App\Domains\Currency\Services\ExchangeRateUpdater;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('currency:fetch-rates')]
#[Description('Fetch exchange rates from the configured provider')]
class FetchRatesCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ExchangeRateUpdater $updater): int
    {
        try {
            $result = $updater->refresh();
        } catch (ExchangeRateProviderFailed $exception) {
            Log::warning('currency.fetch_failed', ['reason' => $exception->getMessage()]);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(json_encode($result->summary(), JSON_THROW_ON_ERROR));

        return self::SUCCESS;
    }
}
