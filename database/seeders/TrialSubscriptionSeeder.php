<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Services\SubscriptionService;
use Illuminate\Database\Seeder;

class TrialSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['vat_number' => 'BE0999999999'],
            [
                'name' => 'Test Trial Company',
                'address' => 'Teststraat 1',
                'postal_code' => '2000',
                'city' => 'Antwerpen',
                'country' => 'BE',
                'email_notifications_enabled' => true,
            ]
        );

        if (! $company->subscription()->exists()) {
            app(SubscriptionService::class)->startTrial($company);
        }
    }
}

