<?php

namespace App\Console\Commands\Customers;

use App\Domains\Customers\Services\CustomerPrivacyService;
use App\Domains\Identity\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('customers:export {customer : The customer public id}')]
#[Description('Write a customer\'s personal data export (SECURITY.md section 12) to the private disk')]
class ExportCustomerCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(CustomerPrivacyService $privacy): int
    {
        $customer = User::query()->where('public_id', (string) $this->argument('customer'))->where('type', 'customer')->first();

        if ($customer === null) {
            $this->error('No customer has this id.');

            return self::FAILURE;
        }

        $path = 'privacy-exports/customer-'.$customer->public_id.'-'.now()->format('YmdHis').'.json';
        Storage::disk('local')->put($path, (string) json_encode($privacy->export($customer), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info("Written to the private disk: {$path}");

        return self::SUCCESS;
    }
}
