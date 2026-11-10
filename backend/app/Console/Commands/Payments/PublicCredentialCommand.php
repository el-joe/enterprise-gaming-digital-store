<?php

namespace App\Console\Commands\Payments;

use App\Domains\Payments\Services\PaymentPublicCredentials;
use App\Domains\Shared\Enums\EnvironmentMode;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('payments:public-key
    {gateway : Gateway key, e.g. stripe}
    {value? : The publishable value, e.g. pk_test_... (omit with --clear)}
    {--mode=sandbox : sandbox or production}
    {--clear : Remove the value}')]
#[Description('Set the publishable provider identifier (Stripe publishable key, PayPal client id, Paymob public key) published by GET /api/v1/app/config')]
class PublicCredentialCommand extends Command
{
    public function handle(PaymentPublicCredentials $credentials): int
    {
        $mode = EnvironmentMode::tryFrom((string) $this->option('mode'));

        if ($mode === null) {
            return $this->failWith('Mode must be sandbox or production.');
        }

        $value = $this->option('clear') ? null : $this->argument('value');

        if ($value === null && ! $this->option('clear')) {
            return $this->failWith('Give the value, or --clear.');
        }

        try {
            $credentials->set((string) $this->argument('gateway'), $mode, $value === null ? null : trim((string) $value));
        } catch (ApplicationException $exception) {
            return $this->failWith(implode(' ', [$exception->getMessage(), ...array_merge(...array_values($exception->errors))]));
        }

        $this->info($value === null ? 'Public credential removed.' : 'Public credential saved.');

        return self::SUCCESS;
    }

    private function failWith(string $message): int
    {
        $this->error($message);

        return self::FAILURE;
    }
}
