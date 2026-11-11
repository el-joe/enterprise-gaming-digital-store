<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed reference data only. SuperAdminSeeder and DemoCatalogSeeder are run explicitly with
     * --class because the first needs environment credentials and the second is sample data.
     */
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class,
            CountrySeeder::class,
            RegionSeeder::class,
            RolePermissionSeeder::class,
            SettingsSeeder::class,
            PaymentGatewaySeeder::class,
            SupplierSeeder::class,
            DeliveryFieldDefinitionSeeder::class,
            HomepageSectionSeeder::class,
            SeoTemplateSeeder::class,
            AutomationWorkflowSeeder::class,
            SuperAdminSeeder::class,
        ]);
    }
}
