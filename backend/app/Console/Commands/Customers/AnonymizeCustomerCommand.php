<?php

namespace App\Console\Commands\Customers;

use App\Domains\Customers\Services\CustomerPrivacyService;
use App\Domains\Identity\Models\User;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('customers:anonymize {customer : The customer public id} {--reason= : Why (audited)} {--force : Do not ask for confirmation}')]
#[Description('Erase a customer\'s personal data, keeping financial records (SECURITY.md section 12)')]
class AnonymizeCustomerCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(CustomerPrivacyService $privacy): int
    {
        $customer = User::query()->where('public_id', (string) $this->argument('customer'))->where('type', 'customer')->first();
        $reason = trim((string) $this->option('reason'));

        if ($customer === null || mb_strlen($reason) < 10) {
            $this->error($customer === null ? 'No customer has this id.' : 'Give --reason with at least 10 characters.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Erase {$customer->public_id}? This cannot be undone.")) {
            return self::FAILURE;
        }

        try {
            $privacy->anonymize($customer, $reason);
        } catch (ApplicationException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Customer erased; financial records kept.');

        return self::SUCCESS;
    }
}
